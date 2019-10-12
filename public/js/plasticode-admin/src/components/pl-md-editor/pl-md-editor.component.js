export const plMdEditor = {
    controller: plMdEditorController,
    controllerAs: 'vm',
    template: '<textarea name="{{vm.name}}" id="{{vm.name}}" ng-model="vm.text" ng-required="vm.isRequired" ng-disabled="vm.isDisabled"></textarea>',
    bindings: {
        value: '<',
        onChange: '&',
        name: '@',
        mdeConfig: '<?',
        isDisabled: '<?',
        isRequired: '<?',
        onPreview: '&?',
        onSearch: '&?'
    }
};

plMdEditorController.$inject = ['$element', '$scope', 'mdEditorService', 'plDataService', '$localStorage'];
function plMdEditorController($element, scope, mdEditorService, plDataService, $localStorage) {
    /* jshint validthis: true */
    let vm = this,
        mde, editorDefaults,
        throttle = plDataService.throttle(_parseAndSearch, 1000);

    vm.text = '';

    vm.$postLink = $postLink;
    vm.$onInit = activate;
    vm.$onChanges = $onChanges;

    ////////////////

    function $postLink() {
        vm.$editor = $element.find('textarea')[0];
        _initEditor();
    }

    function activate() {}

    function $onChanges(changesObj) {
        if (changesObj) {
            if (changesObj.mdeConfig && mde) {
                mde.toTextArea();
                mde = null;
                _initEditor();
            } else {
                if (changesObj.value && changesObj.value.currentValue !== vm.text && mde) {
                    scope.$applyAsync(() => {
                        _setText();
                        mde.codemirror.refresh();
                    });
                }
                if (changesObj.isDisabled) {
                    _toggleReadOnly(vm.isDisabled)
                }
            }
        }
    }

    ////////////////

    function _initEditor() {
        editorDefaults = {
            element: vm.$editor,
            toolbar: mdEditorService.defaultButtons,
            shortcuts: {},
            spellChecker: false,
            status: false,
            forceSync: true,
            autoRefresh: true,
            autoCloseBrackets: angular.isDefined($localStorage.plEditorAutoCloseBrackets) ? $localStorage.plEditorAutoCloseBrackets : true,
            plasticode: {
                preview: false
            }
        };
        scope.$applyAsync(() => {
            mde = new SimpleMDE(Object.assign(editorDefaults, vm.mdeConfig));
            //console.log(mde);

            mdEditorService.toggleAutoCloseBrackets(mde, mde.codemirror.getOption('autoCloseBrackets'));
            _setText();
            _setShortcuts();
            _toggleReadOnly(vm.isDisabled);
            mde.codemirror.on('change', () => {
                scope.$applyAsync(() => {
                    vm.text = mde.value();
                    vm.onChange({value: vm.text, editor: mde});
                });
            });

            if(vm.onSearch && typeof vm.onSearch === 'function')
                mde.codemirror.on('change', (editor) => {
                    console.log('changeEvent');
                    throttle(editor);
                });


            mde.codemirror.on('refresh', () => {
                if(vm.onPreview && typeof vm.onPreview === 'function') {
                    vm.onPreview({isPreview: mde.options.plasticode.preview, editor: mde});
                }
            })
        });
    }

    function _isLink(token = '') {
        if (token && angular.isString(token)) {
            let tokenParts = token.split(' ');

            if (tokenParts.includes('link') ||
                (tokenParts.includes('string') && tokenParts.includes('url'))) {
                return !tokenParts.includes('formatting');
            } else return false;
        } else return false
    }

    function _setShortcuts() {
        let keyMap = {};
        keyMap['Home'] = "goLineLeft";
        keyMap['End'] = 'goLineRight';
        keyMap['Ctrl'] = () => throttle(mde.codemirror);
        if(mde.toolbar) {
            mde.toolbar.forEach(button => {
                if (button.binding !== null) {
                    keyMap[button.binding] = () => button.action(mde);
                }
            });
        }
        mde.codemirror.addKeyMap(keyMap);
    }

    function _setText() {
        vm.text = angular.copy(vm.value);
        if(!vm.text) {
            vm.text = '';
        }
        mde.value(vm.text);
    }

    function _showHint(editor, list, linkMarker) {
        let options = {
            hint: function () {
                let pos = linkMarker.find();
                if(!pos) return;
                //console.log('!', pos);
                //todo: this is hack, should change pos in other parts of code
                pos.from.ch -= 2;
                pos.to.ch += 2;
                return {
                    from: pos.from, to: pos.to, list: list
                }
            },
            completeSingle: false
        };
        editor.showHint(options);
    }

    function _parseAndSearch(editor) {
        let cur = editor.getDoc().getCursor(),
            token = editor.getTokenTypeAt(cur);
        if (_isLink(token)) {
            let linkMarker = _getMarker(editor, cur);
            if (linkMarker && linkMarker.link) {
                //console.log(linkMarker.find(), vm.onSearch);
                let focusedPart; //кусочек ссылки, в котором находится курсор
                if (linkMarker.link.parts.length === 1) {
                    focusedPart = linkMarker.link.parts[0];
                } else {
                    focusedPart = linkMarker.link.parts.find((part) => cur.ch >= part.from.ch && cur.ch <= part.to.ch)
                }
                if(focusedPart && focusedPart.text.length >= 3) {
                    vm.onSearch({text: focusedPart.text}).then(results => {
                        _showHint(editor, results, linkMarker);
                    });
                }
            }
        }
    }

    function _getMarker(editor, cur) {
        let linkMarker,
            activeMarks = editor.findMarksAt(cur);
        //console.log(activeMarks);
        if(!activeMarks.length) {
            linkMarker = _createMarker(editor);
        } else {
            if(activeMarks.length > 1) {
                //под курсором могут оказаться не нужные метки (например, выделение пользователя, фильтруем их)
                let matchingMarks = activeMarks.filter(mark => mark.link);
                if(matchingMarks.length > 1) { //если под курсором оказалось несколько меток значит что-то пошло не так, удаляем их и создаем новую метку
                    matchingMarks.forEach(mark => mark.clear());
                    linkMarker = _createMarker(editor);
                } else {
                    linkMarker = matchingMarks[0];
                }
            } else {
                linkMarker = activeMarks[0];
            }
        }
        if(linkMarker) {
            linkMarker = _parseLink(editor, linkMarker);
        }
        return linkMarker;
    }

    function _createMarker(cm) {
        let cur = cm.getDoc().getCursor(),
            line = cm.getLine(cur.line),
            linkStart = cur.ch, linkEnd = cur.ch - 1;

        if(line) {
            //находим начало и конец ссылки
            while (linkStart > 0 && line.charAt(linkStart - 1) !== '[') {--linkStart;}
            while (linkEnd < line.length && line.charAt(linkEnd) !== ']') {++linkEnd;}
            if (line.substring(linkStart - 2, linkStart) !== '[[' || line.substring(linkEnd, linkEnd + 2) !== ']]') {return;} //dirt check for a link tag

            return cm.markText( {line: cur.line, ch: linkStart}, {line: cur.line, ch: linkEnd}, {inclusiveLeft: true, inclusiveRight: true});
        }
    }

    function _parseLink(cm, marker) {
        let markerPos = marker.find(),
            parsed = {};
        if(!markerPos) {return}

        //Проверяем "целостность" сслыки
        let tagged = cm.getRange({line: markerPos.from.line, ch: markerPos.from.ch - 2}, {line: markerPos.to.line, ch: markerPos.to.ch + 2});
        //console.log(tagged, '|',  tagged.substring(0, 2), '|', tagged.substring(tagged.length - 2, tagged.length), '|',  tagged.substring(2, tagged.length - 2));
        if(!tagged || tagged.substring(0, 2) !== '[[' || tagged.substring(tagged.length - 2, tagged.length) !== ']]') {
            marker.clear();
            return;
        }

        parsed = {
            from: markerPos.from, to: markerPos.to,
            parts: [],
            link: ''
        };

        parsed.link = tagged.substring(2, tagged.length - 2);
        //разбираем ссылку на параметры
        let link = parsed.link,
            line = markerPos.from.line, //ссылка не может переноситься на другую строку
            linkParts = link.split('|');

        let curPos = parsed.from.ch;
        linkParts.forEach((part, idx) => {
            let param = {
                from: {line: line, ch: curPos}, to: {line: line, ch: curPos + part.length},
                text: part
            };
            parsed.parts.push(param);
            curPos += part.length + 1;
        });
        marker.link = parsed;
        return marker;
    }

    function _toggleReadOnly(val) {
        if(!mde) {return}
        mde.codemirror.setOption('readOnly', val ? 'nocursor' : false);
    }
}