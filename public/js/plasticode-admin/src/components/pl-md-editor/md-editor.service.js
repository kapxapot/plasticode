mdEditorService.$inject = [];
export default function mdEditorService() {
    const service = {
        appendButton: appendButton,
        tagWrapButton: tagWrapButton,
        wrapButton: wrapButton,
        defaultButtons: [
            tagWrapButton('boldText', 'fa fa-bold', 'b', 'Полужирный', 'Ctrl-B'),
            tagWrapButton('italicText', 'fa fa-italic', 'i', 'Курсив', 'Ctrl-I'),
            tagWrapButton('strikeText', 'fa fa-strikethrough', 's', 'Зачеркнутый'),
            tagWrapButton('underText', 'fa fa-underline', 'u', 'Подчеркнутый', 'Ctrl-U'),
            '|',
            _customButton('heading-2', SimpleMDE.toggleHeading2, 'fa fa-header fa-header-x fa-header-2', 'Раздел', 'Shift-Ctrl-H'),
            _customButton('heading-3', SimpleMDE.toggleHeading3, 'fa fa-header fa-header-x fa-header-3', 'Подраздел', 'Ctrl-H'),
            '|',
            tagWrapButton('centerText', 'fa fa-align-center', 'center', 'По центру', 'Ctrl-E'),
            tagWrapButton('rightText', 'fa fa-align-right', 'right', 'По правому краю', 'Ctrl-R'),
            '|',
            wrapButton('customLink', 'fa fa-link', '[url=]', '[/url]', 'Ссылка', 'Ctrl-K'),
            tagWrapButton('customImage', 'fa fa-picture-o', 'img', 'Картинка', 'Ctrl-M'),
            tagWrapButton('leftImage', 'fa fa-toggle-left', 'leftimg', 'Картинка слева'),
            tagWrapButton('rightImage', 'fa fa-toggle-right', 'rightimg', 'Картинка справа'),
            tagWrapButton('youtube', 'fa fa-youtube-play', 'youtube', 'Видео YouTube', 'Ctrl-Y'),
            _customButton('unorderedList', SimpleMDE.toggleUnorderedList, 'fa fa-list-ul', 'Список', 'Ctrl-L'),
            _customButton('orderedList', SimpleMDE.toggleOrderedList, 'fa fa-list-ol', 'Нумерованный список', 'Ctrl-Alt-L'),
            '|',
            appendButton('cut', 'fa fa-scissors', '[cut]', 'Граница превью (кат)', 'Ctrl-Alt-X'),
            appendButton('clear', 'fa fa-eraser', '[clear]', 'Очистить обрамление текстом'),
            '|',
            tagWrapButton('carousel', 'fa fa-recycle', 'carousel', 'Карусель'),
            tagWrapButton('spoiler', 'fa fa-chevron-right', 'spoiler', 'Спойлер'),
            tagWrapButton('quote', 'fa fa-quote-right', 'quote', 'Цитата')
        ]
    };

    return service;

    ////////////////

    function appendButton(id, cls, append, title, binding = null, text = null) {
        return _customButton(id, function(e) {
            _wrap(e, '', append, text);
        }, cls, title, binding);
    }

    function tagWrapButton(id, cls, tag, title, binding = null, text = null) {
        return _customButton(id, function(e) {
            _tagWrap(e, tag, text);
        }, cls, title, binding);
    }

    function wrapButton(id, cls, pre, post, title, binding = null, text = null) {
        return _customButton(id, function(e) {
            _wrap(e, pre, post, text);
        }, cls, title, binding);
    }

    ////////////////

    function _wrap(editor, pre, post, text = null) {
        var cm = editor.codemirror;
        var output = '';
        var selectedText = cm.getSelection();
        var startPoint = cm.getCursor("start");
        var endPoint = cm.getCursor("end");
        text = text || selectedText || '';

        output = pre + text + post;

        cm.replaceSelection(output);

        startPoint.ch += pre.length;
        if(startPoint !== endPoint) {
            endPoint.ch += pre.length;
        }
        cm.setSelection(startPoint, endPoint);
        cm.focus();
    }

    function _tagWrap(editor, tag, text = null) {
        _wrap(editor, '[' + tag + ']', '[/' + tag + ']', text);
    }

    function _customButton(id, action, cls, title, binding = null) {
        return {
            name: id,
            action: action,
            className: cls,
            title: title,
            binding: binding
        };
    }
}