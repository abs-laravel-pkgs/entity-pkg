@if(config('entity-pkg.DEV'))
    <?php $entity_pkg_prefix = '/packages/abs/entity-pkg/src';?>
@else
    <?php $entity_pkg_prefix = '';?>
@endif

<script type="text/javascript">
	app.config(['$routeProvider', function($routeProvider) {

	    $routeProvider.
	    when('/entity-pkg/entity-type/list', {
	        template: '<entity-type-list></entity-type-list>',
	        title: 'Entity Types',
	    }).
	    when('/entity-pkg/entity-type/add', {
	        template: '<entity-type-form></entity-type-form>',
	        title: 'Add Entity Type',
	    }).
	    when('/entity-pkg/entity-type/edit/:id', {
	        template: '<entity-type-form></entity-type-form>',
	        title: 'Edit Entity Type',
	    }).

	    when('/entity-pkg/entity/list/:entity_type_id', {
	        template: '<entity-list></entity-list>',
	        title: 'Rejection Reason',
	    }).
	    when('/entity-pkg/entity/add/:entity_type_id', {
	        template: '<entity-form></entity-form>',
	        title: 'Add Rejection Reason',
	    }).
	    when('/entity-pkg/entity/edit/:entity_type_id/:id', {
	        template: '<entity-form></entity-form>',
	        title: 'Edit Rejection Reason',
	    });

	}]);

    var entity_type_list_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity-type/list.html')}}";
    var entity_type_form_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity-type/form.html')}}";

    var entity_list_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/list.html')}}";
    var entity_form_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/form.html')}}";
    // var entity_get_form_data_url = "{{url('/entity-pkg/entity/get-form-data')}}";
    //var entity_get_form_data_url = "{{url('/coa-pkg/coa-code/get-form-data/')}}";

</script>
<script type="text/javascript" src="{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity-type/controller.js')}}"></script>
<script type="text/javascript" src="{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/controller.js')}}"></script>
