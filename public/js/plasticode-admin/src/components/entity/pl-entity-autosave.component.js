export const plEntityAutosave = {
    controller: plEntityAutosaveController,
    controllerAs: 'vm',
    template:
        '<div class="row help-block"><div class="col-md-12">' +
        '<span class="text-muted pull-right" ng-if="!vm.saves.length">Автосохранение включено</span>' +
        '<div class="btn-group pull-right" ng-if="vm.saves.length">' +
        '   <button type="button" class="btn btn-link" ng-click="vm.load(vm.saves[0])">Загрузить версию от {{ vm.saves[0].saved_at | date: \'HH:mm:ss\' }}</button>' +
        '   <button ng-if="vm.saves.length >= 2" type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>' +
        '   <ul class="dropdown-menu">' +
        '       <li ng-repeat="save in vm.saves" ng-if="!$first"><a href="javascript:void(0)" ng-click="vm.load(save)">{{ save.saved_at | date: \'HH:mm:ss\' }}</a></li>' +
        '   </ul>' +
        '</div></div></div>'
    ,
    bindings: {
        value: '<',
        entityType: '<',
        entityId: '<',
        entityUpdatedAt: '<',
        onLoad: '&'
    }
};

plEntityAutosaveController.$inject = ['plDataService', '$localStorage'];
function plEntityAutosaveController(plDataService, $localStorage) {
    /* jshint validthis: true */
    let vm = this,
        throttleSave = plDataService.throttle(_save, 30000);

    vm.saves = [];
    vm.curSave = {};
    vm.isFirstSave = true;
    
    vm.$onInit = activate;
    vm.$onChanges = $onChanges;
    vm.load = load;

    ////////////////
    
    function activate() {
        if(!vm.entityId) {
            vm.entityId = 0;
        }
        if(!$localStorage.entitiesSaves) {
            $localStorage.entitiesSaves = {};
        }
        if(!$localStorage.entitiesSaves[vm.entityType]) {
            $localStorage.entitiesSaves[vm.entityType] = [];
        }
        _cleanupStorage(vm.entityType, vm.entityId, vm.entityUpdatedAt);
        vm.saves = _getSaves(vm.entityType, vm.entityId);

        _newSave();

        if(vm.saves.length > 0 && vm.entityId === 0) {
            load(vm.saves[0]);
        }
    }
    
    function $onChanges(changesObj) {
        if(changesObj.entityType || changesObj.entityId) {
            activate();
        } else if(changesObj.value) {
            throttleSave();
        }
    }

    function load(save) {
        vm.onLoad({text: save.value});
        _newSave();
    }

    ////////////////

    function _cleanupStorage(curType, curId, curUpdatedAt) {
        let now = new Date();

        Object.keys($localStorage.entitiesSaves).forEach(function(cIdx) {
            let cat = $localStorage.entitiesSaves[cIdx];
            cat.forEach((entity, eIdx) => {
                if((now.getTime() - new Date(entity.saved_at).getTime()) / 3600000  >= 3) { //more than 3 hours
                    $localStorage.entitiesSaves[cIdx].splice(eIdx, 1);
                }
                if(curType === cIdx && curId === entity.id && entity.updated_at !== curUpdatedAt) {
                    $localStorage.entitiesSaves[cIdx].splice(eIdx, 1);
                }
            })
        });
    }

    function _getSaves(type, id) {
        return $localStorage.entitiesSaves[type] ? $localStorage.entitiesSaves[type].filter((entity, idx) => entity.id === id) : [];
    }

    function _newSave() {
        vm.isFirstSave = true;
        vm.curSave = {
            id: vm.entityId,
            value: vm.value,
            saved_at: null,
            updated_at: vm.entityUpdatedAt
        };
    }
    
    function _save() {
        vm.curSave.value = vm.value;
        vm.curSave.saved_at = new Date();
        if(vm.isFirstSave) {
            $localStorage.entitiesSaves[vm.entityType].unshift(vm.curSave);
            vm.saves.unshift(vm.curSave);
            vm.isFirstSave = false;
        }
    }
}