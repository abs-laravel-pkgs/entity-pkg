app.component('entityList', {
    templateUrl: entity_list_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope, $location) {
        $scope.loading = true;
        var self = this;
        self.hasPermission = HelperService.hasPermission;

        $http({
            url: laravel_routes['getEntityTypeData'],
            method: 'GET',
            params: {
                'entity_type_id': typeof($routeParams.entity_type_id) == 'undefined' ? null : $routeParams.entity_type_id,
            }
        }).then(function(response) {
            self.entity_type = response.data.entity_type;
            console.log(response.data.entity_type);
            $('.dataTables_length select').select2();
        $('.page-header-content .display-inline-block .data-table-title').html(self.entity_type.name +' <span class="badge badge-secondary" id="table_info">0</span>');
        $('.page-header-content .search.display-inline-block .add_close_button').html('<button type="button" class="btn btn-img btn-add-close"><img src="' + image_scr2 + '" class="img-responsive"></button>');
        $('.page-header-content .refresh.display-inline-block').html('<button type="button" class="btn btn-refresh"><img src="' + image_scr3 + '" class="img-responsive"></button>');
        $('.add_new_button').html(
            '<a href="#!/entity-pkg/entity/add/' + $routeParams.entity_type_id + '" type="button" class="btn btn-secondary" dusk="add-btn">' +
            'Add ' + self.entity_type.name +
            '</a>'
        );
        });
        
        var dataTable = $('#entities_list').DataTable({
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
                url: laravel_routes['getEntityList'],
                data: function(d) {
                    d.entity_type_id = $routeParams.entity_type_id;
                }
            },
            columns: [
                { data: 'action', searchable: false, class: 'action' },
                { data: 'name', name: 'entities.name', searchable: true },
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
            $('#entities_list').DataTable().search('').draw();
        });

        $('.btn-refresh').on("click", function() {
            $('#entities_list').DataTable().ajax.reload();
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
                    custom_noty('success', response.data.message);
                    $('#entities_list').DataTable().ajax.reload();
                    $scope.$apply();
                } else {
                    custom_noty('error', response.data.errors);
                }
            });
        }

        //FOR FILTER
        /*$('#entity_code').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#entity_name').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#mobile_no').on('keyup', function() {
            dataTables.fnFilter();
        });
        $('#email').on('keyup', function() {
            dataTables.fnFilter();
        });
        $scope.reset_filter = function() {
            $("#entity_name").val('');
            $("#entity_code").val('');
            $("#mobile_no").val('');
            $("#email").val('');
            dataTables.fnFilter();
        }*/

        $rootScope.loading = false;
    }
});
//------------------------------------------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------------------------------------------
app.component('entityForm', {
    templateUrl: entity_form_template_url,
    controller: function($http, $location, HelperService, $scope, $routeParams, $rootScope) {
        var self = this;
        self.hasPermission = HelperService.hasPermission;
        self.angular_routes = angular_routes;
        fileUpload();
        $http({
            url: laravel_routes['getEntityFormData'],
            method: 'GET',
            params: {
                'id': typeof($routeParams.id) == 'undefined' ? null : $routeParams.id,
                'entity_type_id': typeof($routeParams.entity_type_id) == 'undefined' ? null : $routeParams.entity_type_id,
            }
        }).then(function(response) {
            self.entity = response.data.entity;
            self.entity_type = response.data.entity_type;
            //console.log(response.data.entity);
            self.entity.entity_type_id = $routeParams.entity_type_id;
            self.action = response.data.action;
            self.theme = response.data.theme;
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

        $scope.SelectFile = function(e) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $scope.PreviewImage = e.target.result;
                $scope.$apply();
            };
            reader.readAsDataURL(e.target.files[0]);
        };
        console.log('self');
        console.log(self);
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
                'entity_type_id': {
                    required: true,
                }
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
                        url: laravel_routes['saveEntity'],
                        method: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                    })
                    .done(function(res) {
                        if (res.success) {
                            custom_noty('success', res.message)
                            $location.path('/entity-pkg/entity/list/' + self.entity_type.id);
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
