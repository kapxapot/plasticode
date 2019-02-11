export const plMdEditor = {
    controller: plMdEditorController,
    controllerAs: 'vm',
    template: '<textarea ng-model="vm.text" ng-required="vm.isRequired" ng-disabled="vm.isDisabled"></textarea>',
    bindings: {
        value: '<',
        onChange: '&',
        mdeConfig: '<?',
        isDisabled: '<?',
        isRequired: '<?',
        onPreview: '&?',
        onSearch: '&?'
    }
};

plMdEditorController.$inject = ['$element', '$scope', 'mdEditorService'];
function plMdEditorController($element, scope, mdEditorService) {
    /* jshint validthis: true */
    let vm = this,
        mde, editorDefaults;

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
        //console.log(changesObj);
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
            plasticode: {
                preview: false
            }
        };
        scope.$applyAsync(() => {
            mde = new SimpleMDE(Object.assign(editorDefaults, vm.mdeConfig));
            console.log(mde);

            _setText();
            _setShortcuts();
            _toggleReadOnly(vm.isDisabled);
            mde.codemirror.on('change', () => {
                scope.$applyAsync(() => {
                    vm.text = mde.value();
                    vm.onChange({value: vm.text});
                });
            });

            if(vm.onSearch && typeof vm.onSearch === 'function')
                mde.codemirror.on('cursorActivity', (editor) => {
                    let cur = editor.getDoc().getCursor();
                    console.log(editor.getTokenTypeAt(cur));
                    if (editor.getTokenTypeAt(cur) === 'link') {
                        let parsedLink = _parseLink(editor);
                        if(parsedLink) {
                            console.log(parsedLink, vm.onSearch);
                            if (cur.ch >= parsedLink.parts.label.from.ch && cur.ch <= parsedLink.parts.label.to.ch) { //внутри label
                                vm.onSearch({text: parsedLink.parts.label.text, isParam: false}).then(results => {
                                    _showHint(editor, results, parsedLink.parts.label.from, parsedLink.parts.label.to);
                                })
                            } else if (parsedLink.parts.params.length && cur.ch >= parsedLink.parts.params[0].from.ch && cur.ch <= parsedLink.parts.label.to.ch) { //внутри 1-го парамтра
                                vm.onSearch({text: parsedLink.parts.params[0].text, isParam: true}).then(results => {
                                    _showHint(editor, results, parsedLink.parts.params[0].from, parsedLink.parts.params[0].to);
                                })
                            }
                        }
                    }
                });


            mde.codemirror.on('refresh', () => {
                if(vm.onPreview && typeof vm.onPreview === 'function') {
                    vm.onPreview({isPreview: mde.options.plasticode.preview, editor: mde});
                }
            })
        });
    }

    function _setShortcuts() {
        let keyMap = {};
        if(mde.toolbar) {
            mde.toolbar.forEach(button => {
                if (button.binding !== null) {
                    keyMap[button.binding] = function () {
                        button.action(mde);
                    }
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

    function _showHint(editor, list, from, to) {
        let options = {
            hint: function () {
                return {
                    from: from, to: to, list: list
                }
            }
        };
        editor.showHint(options);
    }

    function _parseLink(cm) {
        let cur = cm.getDoc().getCursor(),
            line = cm.getLine(cur.line),
            linkStart = cur.ch, linkEnd = cur.ch - 1,
            parsed = {};
        //console.log(cur);
        if(line) {
            //находим начало и конец ссылки
            while (linkStart > 0 && line.charAt(linkStart - 1) !== '[') { --linkStart; }
            while (linkEnd < line.length && line.charAt(linkEnd) !== ']') {++linkEnd;}
            if(line.substring(linkStart - 2, linkStart) !== '[[') { console.log(1); return;} //dirt check for a link tag
            parsed = {
                from: {line: cur.line, ch: linkStart},
                to: {line: cur.line, ch: linkEnd},
                parts: {label: {}, params: []},
                link: ''
            };
            parsed.link = cm.getRange(parsed.from, parsed.to);
            //разбираем ссылку на параметры
            let link = parsed.link,
                linkParts = link.split('|');
            if(linkParts.length > 1) {
                //ссылка с парамтреми
                let curPos = parsed.from.ch;
                linkParts.forEach((part, idx) => {
                    let param = {
                        from: {line: cur.line, ch: curPos}, to: {line: cur.line, ch: curPos + part.length}, text: part
                    };
                    if(idx !== linkParts.length - 1) {
                        parsed.parts.params.push(param);
                        curPos += part.length + 1;
                    } else { //last param is a label
                        parsed.parts.label = param;
                    }
                    console.log(cm.getRange(param.from, param.to));
                });
                console.log(linkParts);
            } else {
                //ссылка без параметров
                parsed.parts.label = {
                    from: parsed.from, to: parsed.to,
                    text: link
                }
            }
            return parsed;
        } else return {};
    }

    function _toggleReadOnly(val) {
        if(!mde) {return}
        mde.codemirror.setOption('readOnly', val ? 'nocursor' : false);
    }
}