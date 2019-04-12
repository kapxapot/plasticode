plDataService.$inject = ['$http', 'API', '$q', '$timeout'];
export default function plDataService($http, API, $q, $timeout) {
    const service = {
        getMdeSearchHints: getMdeSearchHints,
        search: search,
        throttle: throttle
    };

    return service;

    ////////////////

    /**
    * @ngdoc method
    * @name plDataService#getMdeSearchHints
    * @description Возвращает результаты поиска в формате хинтов для plMdEditor
    * @param text {string} строка для поиска
    * @return {Promise}
     **/
    function getMdeSearchHints(text) {
        return service.search(text).then(resp => {
            let results = [];
            resp.data.forEach(item => {
                let result = {};
                if (item.data && item.data.category) {
                    result.displayText = item.data.name_ru + ' (' + item.data.category.name_ru + ')';
                } else {
                    result.displayText = item.text;
                }

                result.text = item.code;
                result.className = item.type;

                results.push(result)
            });
            return results;
        })
    }

    /**
     * @ngdoc method
     * @name plDataService#search
     * @description Поиск по строке
     * @param text {string} строка для поиска
     * @return {Promise}
     **/
    function search(text) {
        return $http.get(API + 'search/' + text)
            .then((resp) => resp)
            .catch((err) => $q.reject(err))
    }

    /**
     * @ngdoc method
     * @name plDataService#throttle
     * @description Возвращает обертку, передающую вызов функции func не чаще, чем раз в ms секунд
     * @param func {Function} Задерживаемая функция
     * @param ms {number} Время задержки (мс)
     * @return {Function}
     **/
    function throttle(func, ms) {
        let isThrottled = false,
            savedArgs, savedThis;

        function wrapper() {
            if (isThrottled) {
                savedArgs = arguments;
                savedThis = this;
                return;
            }
            func.apply(this, arguments);
            isThrottled = true;
            $timeout(function() {
                isThrottled = false;
                if (savedArgs) {
                    wrapper.apply(savedThis, savedArgs);
                    savedArgs = savedThis = null;
                }
            }, ms);
        }

        return wrapper;
    }
}