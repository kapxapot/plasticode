/*function wrap(editor, pre, post, text = null) {
    var cm = editor.codemirror;
    var output = '';
    var selectedText = cm.getSelection();
    text = text || selectedText || '';

    output = pre + text + post;
    cm.replaceSelection(output);
}

function tagWrap(editor, tag, text = null) {
    wrap(editor, '[' + tag + ']', '[/' + tag + ']', text);
}

function customButton(id, action, cls, title) {
    return {
    	name: id,
    	action: action,
    	className: cls,
    	title: title
	};
}

function tagWrapButton(id, cls, tag, title, text = null) {
    return customButton(id, function(e) {
        tagWrap(e, tag, text);
    }, cls, title);
}

function wrapButton(id, cls, pre, post, title, text = null) {
    return customButton(id, function(e) {
        wrap(e, pre, post, text);
    }, cls, title);
}

function appendButton(id, cls, append, title, text = null) {
    return customButton(id, function(e) {
        wrap(e, '', append, text);
    }, cls, title);
}

function mdeButtons() {
    var buttons = [
        tagWrapButton('boldText', 'fa fa-bold', 'b', 'Полужирный'),
        tagWrapButton('italicText', 'fa fa-italic', 'i', 'Курсив'),
        tagWrapButton('strikeText', 'fa fa-strikethrough', 's', 'Перечеркнутый'),
        tagWrapButton('underText', 'fa fa-underline', 'u', 'Подчеркнутый'),
        '|',
        customButton('heading-2', SimpleMDE.toggleHeading2, 'fa fa-header fa-header-x fa-header-2', 'Раздел'),
        customButton('heading-3', SimpleMDE.toggleHeading3, 'fa fa-header fa-header-x fa-header-3', 'Подраздел'),
        '|',
        tagWrapButton('centerText', 'fa fa-align-center', 'center', 'По центру'),
        tagWrapButton('rightText', 'fa fa-align-right', 'right', 'По правому краю'),
        '|',
        wrapButton('customLink', 'fa fa-link', '[url=]', '[/url]', 'Ссылка'),
        tagWrapButton('customImage', 'fa fa-picture-o', 'img', 'Картинка'),
        tagWrapButton('leftImage', 'fa fa-toggle-left', 'leftimg', 'Картинка слева'),
        tagWrapButton('rightImage', 'fa fa-toggle-right', 'rightimg', 'Картинка справа'),
        tagWrapButton('youtube', 'fa fa-youtube-play', 'youtube', 'Видео YouTube'),
        customButton('unorderedList', SimpleMDE.toggleUnorderedList, 'fa fa-list-ul', 'Список'),
        customButton('orderedList', SimpleMDE.toggleOrderedList, 'fa fa-list-ol', 'Нумерованный список'),
        '|',
        appendButton('cut', 'fa fa-scissors', '[cut]', 'Граница превью (кат)'),
        appendButton('clear', 'fa fa-eraser', '[clear]', 'Очистить обрамление текстом'),
        '|',
        tagWrapButton('carousel', 'fa fa-recycle', 'carousel', 'Карусель'),
        tagWrapButton('spoiler', 'fa fa-chevron-right', 'spoiler', 'Спойлер'),
        tagWrapButton('quote', 'fa fa-quote-right', 'quote', 'Цитата')
    ];
    
    //"link", "image", "table",

    return buttons;
}*/

/*function setMdeResult(editor, value) {
    var result = $(editor.element).closest('.mde-group').find('.mde-result');
    result.html(value);
}*/

/*function toggleMdeFullscreen(editor) {
    var elem = $(editor.element);
    elem.closest('.modal-dialog').toggleClass('modal-dialog-fullscreen');
    elem.closest('.form-group').toggleClass('short');
    
    var mdeGroup = elem.closest('.mde-group');
    mdeGroup.toggleClass('mde-group-fullscreen');
    mdeGroup.find('.mde-editor').toggleClass('col-sm-6');
    
    var mdeResult = mdeGroup.find('.mde-result');
    mdeResult.toggleClass('hidden');
}*/

/*function getMdeVars(editor) {
    var opts = editor.options;
    if (opts.plasticode === undefined) {
        opts.plasticode = {};
    }
    
    return opts.plasticode;
}

function getMdeVar(editor, name, def = null) {
    var vars = getMdeVars(editor);
    return (vars[name] !== undefined) ? vars[name] : def;
}

function setMdeVar(editor, name, val) {
    var vars = getMdeVars(editor);
    vars[name] = val;
}

function toggleMdeVar(editor, name) {
    var val = getMdeVar(editor, name, false);
    setMdeVar(editor, name, !val);
}*/
