httpInterceptor.$inject = ['$q', '$injector'];
export function httpInterceptor ($q) {
    return {
        request: (config) => {
            if (!config.method) {
                config.method = 'post';
            }
            return config
        },
        response: (response) => {
            return $q.when(response);
        },
        responseError: (rejection) => {
            return $q.reject(rejection);
        }
    }
}