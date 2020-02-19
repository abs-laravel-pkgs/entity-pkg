app.component('entityTypeList', {
    templateUrl: entity_type_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.entity_type_list = self.status_list = [];
        self.entity_type_data = self.status_data = '';
        $http({
            url: laravel_routes['getEntityTypeFilterData'],
            method: 'GET',
            params: {
                'entity_type_id': typeof($routeParams.entity_type_id) == 'undefined' ? null : $routeParams.entity_type_id,
            }
        }).then(function(response) {
            self.entity_type_list = response.data.entity_type_list;
            self.entity_type_list.unshift({'id' : '','name' :  'Select Entity Type'});
            self.status_list = [{'id' : '','name' :  'Select Status'},{'id' : 0,'name' :  'Active'},{'id' : 1,'name' :  'Inactive'}];
            console.log(response.data);
            console.log(self.status_list);
            $('.dataTables_length select').select2();
        $('.page-header-content .display-inline-block .data-table-title').html('Entity Types <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        $('.add_new_button').html(
            '<a href="#!/entity-pkg/entity-type/add/" type="button" class="btn btn-secondary" dusk="add-btn">' +
            'Add Entity Type' +
            '</a><div class="page-header-content button-block"><button class="btn btn-bordered" data-toggle="modal" data-target="#entity-type-filter-modal"><i class="icon ion-md-funnel"></i>Filter</button></div>'
        );
            $('.btn-add-close').on("click", function() {
                $('#entity_types_list').DataTable().search('').draw();
            });

            $('.btn-refresh').on("click", function() {
                $('#entity_types_list').DataTable().ajax.reload();
            });

            app.filter('removeString', function () {
                return function (text) {
                    var str = text.replace('s', '');
                    return str;
                };
            });
            $scope.entityChange = function(){
                $('#entity_types_list').DataTable().draw();
            }
            $scope.entityStatus = function(){
                $('#entity_types_list').DataTable().draw();
            }
            $scope.reset_filter = function() {
                self.entity_type_data = self.status_data = '';
                $('#entity_types_list').DataTable().draw();
            }
        });

        var dataTable = $('#entity_types_list').DataTable({
            "dom": dom_structure,
            "language": {
                "search": "",
                "searchPlaceholder": "Search",
                "lengthMenu": "Rows Per Page _MENU_",
                "paginate": {
                    "next": '<i class="icon ion-ios-arrow-forward"></i>',
                    "previous": '<i class="icon ion-ios-arrow-back"></i>'
                },
            },
            stateSave: true,
            pageLength: 10,
            processing: true,
            serverSide: true,
            paging: true,
            ordering: false,
            ajax: {
                url: laravel_routes['getEntityTypeList'],
                data: function(d) {
                    //d.entity_type_type_id= $routeParams.entity_type_type_id;
                    d.entity_type_id= self.entity_type_data;
                    d.status_id= self.status_data;
                }
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'name', name: 'entity_types.name', searchable: true },
            ],
            "infoCallback": function(settings, start, end, max, total, pre) {
                $('#table_info').html(total + '/' + max)
            },
            rowCallback: function(row, data) {
                $(row).addClass('highlight-row');
            },
            initComplete: function() {
                $('.search label input').focus();
            },
        });
        

        $('.btn-add-close').on("click", function() {
            $('#entity_types_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#entity_types_list').DataTable().ajax.reload();
        });

        //DELETE
        $scope.deleteEntityType = function($id) {
            self.entitytype_id = $id;
        }
        $scope.deleteConfirm = function($id) {
            //$id = $('#entity_type_id').val();
            $http.get(
                laravel_routes['deleteEntityType'], {
                    params: {
                        id: $id,
                    }
                }
            ).then(function(response) {
                if (response.data.success) {
                    custom_noty('success', response.data.message);
                    $('#entity_types_list').DataTable().ajax.reload();
                    $scope.$apply();
                } else {
                    custom_noty('error', response.data.errors);
                }
            });
        }

        //FOR FILTER
        /*$('#entity_type_code').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#entity_type_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#mobile_no').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#email').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.reset_filter = function() {
            $("#entity_type_name").val('');
            $("#entity_type_code").val('');
            $("#mobile_no").val('');
            $("#email").val('');
            dataTables.fnFilter();
        }*/

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('entityTypeForm', {
    templateUrl: entity_type_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        fileUpload();
        $http({
            url: laravel_routes['getEntityTypeFormData'],
            method: 'GET',
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
            }
        }).then(function(response) {
            console.log(response);
            self.entity_type = response.data.entity_type;
            self.entities = response.data.entities;
            self.action = response.data.action;
            self.theme = response.data.theme;
            self.removed_entity_ids =[];
            $rootScope.loading = false;
            if (self.action == 'Edit') {
                if (self.entity_type.deleted_at) {
                    self.switch_value = 'Inactive';
                } else {
                    self.switch_value = 'Active';
                }
            } else {
                $scope.addEntity();
                self.switch_value = 'Active';
            }
            //console.log(response.data.entities);
            angular.forEach(response.data.entities, function (value, key) { 
                if (value.deleted_at) {
                    value.switch_value = 'Inactive';
                } else {
                    value.switch_value = 'Active';
                }
                //$scope.names.push(value.name); 
            });
        });

        $scope.SelectFile = function(e) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $scope.PreviewImage = e.target.result;
                $scope.$apply();
            };
            reader.readAsDataURL(e.target.files[0]);
        };
        $scope.removeEntity = function(index,entity_id){
            self.removed_entity_ids.push(entity_id);
            self.entities.splice(index, 1); 
        }

        $scope.addEntity = function(){
            self.entities.push({
                switch_value: 'Active',
            });
        }
        $('.btn-nxt').on("click", function() {
            $('.editDetails-tabs li.active').next().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-prev').on("click", function() {
            $('.editDetails-tabs li.active').prev().children('a').trigger("click");
            tabPaneFooter();
        });
        $('.btn-pills').on("click", function() {
            tabPaneFooter();
        });
        var form_id = '#form';
        var v = jQuery(form_id).validate({
            ignore: '',
            errorPlacement: function(error, element) {
                error.insertAfter(element);
            },
            rules: {
                'name': {
                    required: true,
                    minlength: 3,
                    maxlength: 191,
                },
            },
            messages: {
                // 'logo_id': {
                //     extension: "Accept Image Files Only. Eg: jpg,jpeg,png,ico,bmp,svg,gif"
                // }
            },
            submitHandler: function(form) {
                let formData = new FormData($(form_id)[0]);
                $('#submit').button('loading');
                $.ajax({
                        url: laravel_routes['saveEntityType'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success) {
                            custom_noty('success', res.message)
                            $location.path('/entity-pkg/entity-type/list');
                            $scope.$apply();
                        } else {
                            $('#submit').button('reset');
                            var errors = '';
                            for (var i in res.errors) {
                                errors += '<li>' + res.errors[i] + '</li>';
                            }
                            custom_noty('error', errors);
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
