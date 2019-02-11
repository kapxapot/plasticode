import {httpInterceptor} from "./pl-http-interceptor.factory";
import plDataService from "./data.service"

export default angular.module('plasticodeCore', [])
    .config(PlasticodeConfig)
    .factory('plasticodeHttpInterceptor', httpInterceptor)
    .service('plDataService', plDataService)

PlasticodeConfig.$inject = ['$httpProvider'];
function PlasticodeConfig($httpProvider) {
    $httpProvider.interceptors.push('plasticodeHttpInterceptor');
}