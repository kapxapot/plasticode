import './scss/app.scss'

import 'plasticode-core'
import 'simplemde'
import {plMdEditor} from "./components/pl-md-editor/pl-md-editor.component";
import mdEditorService from './components/pl-md-editor/md-editor.service'
import plEntityService from "./components/entity/pl-entity.service";

const dependencies = [
    'plasticodeCore'
];

const plAdmin = angular.module('plasticodeAdmin', dependencies)
    .value('API', '/')
    .service('mdEditorService', mdEditorService)
    .service('plEntityService', plEntityService)
    .component('plMdEditor', plMdEditor);