@if(config('entity-pkg.DEV'))
    <?php $entity_pkg_prefix = '/packages/abs/entity-pkg/src';?>
@else
    <?php $entity_pkg_prefix = '';?>
@endif

<script type="text/javascript">
    var entity_list_template_url = "{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/entities.html')}}";
</script>
<script type="text/javascript" src="{{asset($entity_pkg_prefix.'/public/themes/'.$theme.'/entity-pkg/entity/controller.js')}}"></script>
