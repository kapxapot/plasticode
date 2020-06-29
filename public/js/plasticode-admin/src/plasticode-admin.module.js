import './scss/app.scss'

import 'plasticode-core'
import 'simplemde'
import 'ngstorage'
import {plMdEditor} from "./components/pl-md-editor/pl-md-editor.component";
import mdEditorService from './components/pl-md-editor/md-editor.service'
import plEntityService from "./components/entity/pl-entity.service";
import {plEntityAutosave} from "./components/entity/pl-entity-autosave.component";

const dependencies = [
    'plasticodeCore',
    'ngStorage'
];

const plAdmin = angular.module('plasticodeAdmin', dependencies)
    .value('API', API_ENDPOINT ? API_ENDPOINT : '/')
    .service('mdEditorService', mdEditorService)
    .service('plEntityService', plEntityService)
    .component('plMdEditor', plMdEditor)
    .component('plEntityAutosave', plEntityAutosave);
