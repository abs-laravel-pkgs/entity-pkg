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
	        title: 'Entity List',
	    }).
	    when('/entity-pkg/entity/add/:entity_type_id', {
	        template: '<entity-form></entity-form>',
	        title: 'Add Entity',
	    }).
	    when('/entity-pkg/entity/edit/:id', {
	        template: '<entity-form></entity-form>',
	        title: 'Edit Entity',
	    });

	}]);

    var entity_type_list_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity-type/list.html')}}";
    var entity_type_form_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity-type/form.html')}}";

    var entity_list_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/list.html')}}";
    var entity_form_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/form.html')}}";

</script>
<script type="text/javascript" src="{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity-type/controller.js')}}"></script>
<script type="text/javascript" src="{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/controller.js')}}"></script>
