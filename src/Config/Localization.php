<?php

namespace Plasticode\Config;

class Localization
{
    public function get($lang)
    {
        if (!$lang || $lang == 'en') {
            return null;
        }
        
        if (is_callable(array($this, $lang))) {
            return $this->$lang();
        }
        
        throw new \Exception("Dictionary for language '{$lang}' not found. Define it in Localization class.");
    }
        
    protected function ru()
    {
        return [
            // messages
            '{{name}} must contain only letters (a-z) and digits (0-9)' => "Поле '{{name}}' должно содержать только латинские буквы (a-z) и цифры (0-9).",
            '{{name}} must have a length greater than {{minValue}}' => "Поле '{{name}}' должно быть не менее {{minValue}} символов.",
            '{{name}} must have a length between {{minValue}} and {{maxValue}}' => "Поле '{{name}}' должно быть от {{minValue}} до {{maxValue}} символов.",
            '{{name}} must not contain whitespace' => "Поле '{{name}}' должно быть без пробелов.",
            '{{name}} must not be empty' => "Поле '{{name}}' не должно быть пустым.",
            '{{name}} must be positive' => "Поле '{{name}}' должно быть больше нуля.",
            '{{name}} must not be blank' => "Поле '{{name}}' не может быть пустым.",
            '{{name}} must be numeric' => "Поле '{{name}}' должно быть числом.",
            '{{name}} must validate against {{regex}}' => "Поле '{{name}}' должно соответствовать регулярному выражению {{regex}}.",
            'E-mail address already exists.' => "Указанный почтовый адрес уже занят.",
            'Login already exists.' => "Указанный логин уже занят.",
            'Incorrect password.' => "Неверный пароль.",
            'Incorrect security token.' => "Неверный токен безопасности.",
            'Security token expired.' => "Истек срок действия токена безопасности.",
            'Incorrect or expired captcha.' => "Неверная или устаревшая капча.",
            'Registration successful.' => "Вы успешно зарегистрировались.",
            'Incorrect user/password.' => "Пользователь с такими данными не найден.",
            'Login successful.' => "Вы успешно вошли.",
            'Password change successful.' => "Пароль успешно изменен.",
            'No image.' => "Картинка не выбрана.",
            'Incorrect image type.' => "Неверный тип изображения.",
            'Access denied.' => "Отказано в доступе.",
            'Insufficient access rights.' => "Недостаточно прав.",
            'Not found.' => "Не найдено.",
            'Server error.' => "Ошибка на сервере.",
            'Parent entity creates recursion.' => "Родительская сущность создает рекурсию.",
            'Data already changed. Please reload page.' => "Данные уже изменены. Перезагрузите страницу.",
            'Upload successful.' => "Загрузка успешно завершена.",

            // fields
            'login' => 'Логин',
            'password' => 'Пароль',
            'name' => 'Имя',
            'position' => 'Позиция',
            'link' => 'Ссылка',
            'text' => 'Текст',
            'icon' => 'Иконка',
            'alias' => 'Алиас',
            'description' => 'Описание',

            'news_forum_id' => 'Id новостного форума',
            'main_forum_id' => 'Id игрового форума',
            'name_ru' => 'Заголовок',
            'name_en' => 'Английский заголовок',
            'comment' => 'Заголовок',
            'title' => 'Название',
            'stream_id' => 'Код',
            
            // 404
            'Page not found or moved somewhere we don\'t know where.' => 'Страница не найдена или перемещена.',
            'Error 404' => 'Ошибка 404',
        ];
    }
}
