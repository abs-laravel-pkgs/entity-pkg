app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    when('/entity-pkg/entity/list/:entity_type_id', {
        template: '<entity-list></entity-list>',
        title: 'Rejection Reasons',
    }).
    when('/entity-pkg/entity/add', {
        template: '<entity-form></entity-form>',
        title: 'Add Rejection Reason',
    }).
    when('/entity-pkg/entity/edit/:id', {
        template: '<entity-form></entity-form>',
        title: 'Edit Rejection Reason',
    });
}]);

app.component('entityList', {
    templateUrl: entity_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location, $element, $cookies) {
        console.log($routeParams);
        $scope.loading = true;
        $('#search_entity').focus();
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.add_permission = self.hasPermission('add-entity');
        if (!self.hasPermission('entities')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        var entity_name = $cookies.get('entity_name');
        $('#entity_name').val(entity_name);
        self.status = $cookies.get('status');
        $('#status_id').val(self.status);
        $('#search_entity').val($cookies.get('search_entity'));
        self.entity_type_id = $routeParams.entity_type_id;
        var table_scroll;
        table_scroll = $('.page-main-content.list-page-content').height() - 37;
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
            //ordering: false,
            scrollY: table_scroll + "px",
            scrollCollapse: true,
            ajax: {
                url: laravel_routes['getEntityList'],
                type: "GET",
                dataType: "json",
                data: function(d) {
                    d.entity_type_id = $routeParams.entity_type_id;
                    d.entity_name = $('#entity_name').val();
                    d.status_id = $('#status').val();
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
            $cookies.put('search_entity', $('#search_entity').val());
            $('#entities_list').DataTable().search('').draw();
        }

        var dataTables = $('#entities_list').dataTable();
        $("#search_entity").keyup(function() {
            $cookies.put('search_entity', $('#search_entity').val());
            dataTables.fnFilter(this.value);
        });

        //DELETE
        $scope.deleteEntity = function($id) {
            $('#entity_id').val($id);
        }
        $scope.deleteConfirm = function() {
            $id = $('#entity_id').val();
            $http.get(
                laravel_routes['deleteEntity'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', 'JV Rejection Reason Deleted Successfully');
                    $location.path('/entity-pkg/entity/list/' + self.entity_type_id);
                    $('#entities_list').DataTable().ajax.reload(function(json) {});
                }
            });
        }

        //FOR FILTER
        self.status = [
            { id: '', name: 'Select Status' },
            { id: '1', name: 'Active' },
            { id: '0', name: 'Inactive' },
        ];
        $element.find('input').on('keydown', function(ev) {
            ev.stopPropagation();
        });
        $scope.clearSearchTerm = function() {
            $scope.searchTerm = '';
        };
        /* Modal Md Select Hide */
        $('.modal').bind('click', function(event) {
            if ($('.md-select-menu-container').hasClass('md-active')) {
                $mdSelect.hide();
            }
        });

        $('#entity_name').on('keyup', function() {
            $cookies.put('entity_name', entity_name);
            dataTables.fnFilter();
        });
        $scope.onSelectedStatus = function(val) {
            $("#status").val(val);
            $cookies.put('status', self.status);
            dataTables.fnFilter();
        }

        $scope.reset_filter = function() {
            $("select#status").val('');
            $cookies.put('status', '');
            $cookies.put('entity_name', '');
            $("#entity_name").val('');
            $("#status").val('');
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
        $('#name').focus();
        console.log($routeParams.id);
        self.entity_type_id = $routeParams.entity_type_id;
        self.hasPermission = HelperService.hasPermission;
        if (!self.hasPermission('add-entity') || !self.hasPermission('edit-entity')) {
            window.location = "#!/page-permission-denied";
            return false;
        }
        self.angular_routes = angular_routes;
        $http.get(
            laravel_routes['getEntityFormData'], {
                params: {
                    id: typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                    entity_type_id: $routeParams.entity_type_id,
                }
            }
        ).then(function(response) {
            self.entity = response.data.entity;
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
                    maxlength: 191,
                },
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
                            custom_noty('success', res.message);
                            $location.path('/entity-pkg/entity/list/' + self.entity_type_id);
                            $scope.$apply();
                        } else {
                            if (!res.success == true) {
                                $('#submit').button('reset');
                                var errors = '';
                                for (var i in res.errors) {
                                    errors += '<li>' + res.errors[i] + '</li>';
                                }
                                custom_noty('error', errors);
                            } else {
                                $('#submit').button('reset');
                                $location.path('/entity-pkg/entity/list/' + self.entity_type_id);
                                $scope.$apply();
                            }
                        }
                    })
                    .fail(function(xhr) {
                        $('#submit').button('reset');
                        custom_noty('error', 'Something went wrong at server');
                    });
            }
        });
    }
});