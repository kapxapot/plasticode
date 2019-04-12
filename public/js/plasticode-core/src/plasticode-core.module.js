import {httpInterceptor} from "./pl-http-interceptor.factory";
import plDataService from "./data.service"

export default angular.module('plasticodeCore', [])
    .config(PlasticodeConfig)
    .factory('plasticodeHttpInterceptor', httpInterceptor)
    .service('plDataService', plDataService)

PlasticodeConfig.$inject = ['$httpProvider', '$compileProvider'];
function PlasticodeConfig($httpProvider, $compileProvider) {
    $httpProvider.interceptors.push('plasticodeHttpInterceptor');
    $compileProvider.aHrefSanitizationWhitelist(/^\s*(https?|ftp|mailto|file|skype|callto|javascript):|data:image/);
}