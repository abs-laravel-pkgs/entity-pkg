app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/entity-pkg/entity/list/:entity_type_id', {
        template: '<entity-list></entity-list>',
        title: 'Entitys',
    }).
    when('/entity-pkg/entity/add', {
        template: '<entity-form></entity-form>',
        title: 'Add Entity',
    }).
    when('/entity-pkg/entity/edit/:id', {
        template: '<entity-form></entity-form>',
        title: 'Edit Entity',
    });
}]);

app.component('entityList', {
    templateUrl: entity_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        console.log($routeParams);
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        var table_scroll;
        self.entity_type_id = $routeParams.entity_type_id;
        table_scroll = $('.page-main-content').height() - 37;
        var dataTable = $('#entities_list').DataTable({
            "dom": cndn_dom_structure,
            "language": {
                // "search": "",
                // "searchPlaceholder": "Search",
                "lengthMenu": "Rows _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            pageLength: 10,
            processing: true,
            stateSaveCallback: function(settings, data) {
                localStorage.setItem('CDataTables_' + settings.sInstance, JSON.stringify(data));
            },
            stateLoadCallback: function(settings) {
                var state_save_val = JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
                if (state_save_val) {
                    $('#search_entity').val(state_save_val.search.search);
                }
                return JSON.parse(localStorage.getItem('CDataTables_' + settings.sInstance));
            },
            serverSide: true,
            paging: true,
            stateSave: true,
            ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: url(laravel_routes['getEntityList']),
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.entity_type_id = $routeParams.entity_type_id;
                    d.entity_name = $('#entity_name').val();
                    d.status_id = self.status_id;
                },
            },

            columns: [
                { data: 'action', class: 'action', name: 'action', searchable: false },
                { data: 'name', name: 'entities.name' },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                //$('#table_info').html(total)
                $('#table_info').html(total)
                $('.foot_info').html('Showing ' + start + ' to ' + end + ' of ' + max + ' entries')
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            }
        });
        $('.dataTables_length select').select2();

        $scope.clear_search = function() {
            $('#search_entity').val('');
            $('#entities_list').DataTable().search('').draw();
        }

        var dataTables = $('#entities_list').dataTable();
        $("#search_entity").keyup(function() {
            dataTables.fnFilter(this.value);
        });
        self.entity_type_id = $routeParams.entity_type_id;

        //DELETE
        $scope.deleteEntity = function($id) {
            $('#entity_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#entity_id').val();
            $http.get(
                laravel_routes['deleteEntity'],{
                    params:{
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    $noty = new Noty({
                        type: 'success',
                        layout: 'topRight',
                        text: 'JV Rejection Reason Deleted Successfully',
                    }).show();
                    setTimeout(function() {
                        $noty.close();
                    }, 3000);
                    $location.path('/entity-pkg/entity/list/'+self.entity_type_id);
                    $('#entities_list').DataTable().ajax.reload(function(json) {});
                }
            });
        }

        $('#entity_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.statusChange=function(){
            dataTables.fnFilter();
        }
        
        $scope.reset_filter = function() {
            $("#entity_name").val('');
            self.status_id ='';
            dataTables.fnFilter();
        }

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('entityForm', {
    templateUrl: entity_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        // console.log(entity_get_form_data_url);
        // get_form_data_url = typeof($routeParams.id) == 'undefined' ? entity_get_form_data_url : entity_get_form_data_url + '/' + $routeParams.id;
        // console.log(get_form_data_url);
        var self = this;
        console.log($routeParams.id);
        self.entity_type_id = $routeParams.entity_type_id;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getEntityFormData'],{
                params:{
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                    entity_type_id: $routeParams.entity_type_id,
                }
            }
        ).then(function(response) {
            self.entity = response.data.entity;
            self.attachment = response.data.address;
            self.entity_type_id = self.entity_type_id;
            self.action = response.data.action;
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.entity.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                self.switch_value = 'Active';
               /* self.state_list = [{ 'id': '', 'name': 'Select State' }];
                self.city_list = [{ 'id': '', 'name': 'Select City' }];*/
            }
        });

        /* Tab Funtion */
        $('.btn-nxt').on("click", function() {
            $('.cndn-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.cndn-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        $scope.btnNxt = function() {}
        $scope.prev = function() {}

        /*$scope.saveFormData =  function(){

        }*/
        var form_id = '#entity_form';
        var v = jQuery(form_id).validate({
            ignore: '',
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 255,
                },
            },
            messages: {
                'name': {
                    maxlength: 'Maximum of 255 charaters',
                },
            },
            invalidHandler: function(event, validator) {
                $noty = new Noty({
                    type: 'error',
                    layout: 'topRight',
                    text: 'You have errors,Please check all tabs'
                }).show();
                setTimeout(function() {
                    $noty.close();
                }, 3000)
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEntity'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success == true) {
                            $noty = new Noty({
                                type: 'success',
                                layout: 'topRight',
                                text: res.message,
                            }).show();
                            setTimeout(function() {
                                $noty.close();
                            }, 3000);
                            $location.path('/entity-pkg/entity/list/'+self.entity_type_id);
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                $noty = new Noty({
                                    type: 'error',
                                    layout: 'topRight',
                                    text: errors
                                }).show();
                                setTimeout(function() {
                                    $noty.close();
                                }, 3000);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/entity-pkg/entity/list/'+self.entity_type_id);
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        $noty = new Noty({
                            type: 'error',
                            layout: 'topRight',
                            text: 'Something went wrong at server',
                        }).show();
                        setTimeout(function() {
                            $noty.close();
                        }, 3000);
                    });
            }
        });
    }
});