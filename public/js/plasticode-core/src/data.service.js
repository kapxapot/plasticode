plDataService.$inject = ['$http', 'API', '$q'];
export default function plDataService($http, API, $q) {
    const service = {
        search: search
    };

    return service;

    ////////////////

    function search(text) {
        return $http.get('/dev/search/' + text)
            .then((resp) => resp)
            .catch((err) => $q.reject(err))
    }
}