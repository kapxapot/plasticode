plEntityService.$inject = ['$http', 'API', '$q'];
export default function plEntityService($http, API, $q) {
    const service = {
        parse: parse
    };

    return service;

    ////////////////

    function parse(text) {
        return $http.post(API + 'parser/parse', {text: text})
            .then((resp) => resp.data)
            .catch((err) => $q.reject(err))
    }
}