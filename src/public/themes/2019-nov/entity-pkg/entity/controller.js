app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/entities', {
        template: '<entities></entities>',
        title: 'Entitys',
    });
}]);

app.component('entities', {
    templateUrl: entity_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        $http({
            url: laravel_routes['getEntitys'],
            method: 'GET',
        }).then(function(response) {
            self.entities = response.data.entities;
            $rootScope.loading = false;
        });
        $rootScope.loading = false;
    }
});