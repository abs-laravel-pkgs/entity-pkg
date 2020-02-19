<?php

namespace Abs\EntityPkg;
use Abs\Basic\Attachment;
use Abs\EntityPkg\Entity;
use Abs\EntityPkg\EntityType;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class EntityController extends Controller {

	private $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		$this->company_id = config('custom.company_id');
	}

	public function getEntitys(Request $request) {
		$this->data['entities'] = Entity::
			select([
			'entities.question',
			'entities.answer',
		])
			->where('entities.company_id', $this->company_id)
			->orderby('entities.display_order', 'asc')
			->get()
		;
		$this->data['success'] = true;

		return response()->json($this->data);

	}
	public function getEntityTypeData(Request $r){
		$this->data['entity_type'] = $entity_type = EntityType::/*withTrashed()->*/find($r->entity_type_id);
		$this->data['entity_list'] =  Entity::withTrashed()->where('entity_type_id',$r->entity_type_id)->get();
		//$this->data['status_list'] = [['Select Status'],['Active'],['Inactive']];
		//dd($entity_type);
		if($entity_type){
			$this->data['success'] = true;
			return response()->json($this->data);
		}else{
			return response()->json(['success' => false]);
		}
	}
	public function getEntityList(Request $r) {
		$entities = Entity::withTrashed()
			->select([
				'entities.id',
				'entities.name',
				'entities.entity_type_id',
				DB::raw('IF(entities.deleted_at IS NULL, "Active","Inactive") as status'),

			])
			->where('entities.company_id', $this->company_id)
			->where('entities.entity_type_id', $r->entity_type_id);

		/*->where(function ($query) use ($request) {
				if (!empty($request->question)) {
					$query->where('entities.question', 'LIKE', '%' . $request->question . '%');
				}
			})*/
			
			if(isset($r->entity_id)){
				if($r->entity_id){
					$entities = $entities->where('entities.id',$r->entity_id);
				}
			}
			if(isset($r->status_id)){
				if($r->status_id==0 || $r->status_id==1){
					$entities = $r->status_id ? $entities->whereNotNull('deleted_at') : $entities->whereNull('deleted_at');
				}
			}
			$entities =  $entities->orderby('entities.id', 'desc');
		return Datatables::of($entities)
			->rawColumns(['action', 'name'])
			->addColumn('name', function ($entities) {
				//$status = $entities->status == 'Active' ? 'green' : 'red';
				return /*'<span class="status-indicator ' . $status . '"></span>' . */$entities->name;
			})
			->addColumn('action', function ($entities) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/entity-pkg/entity/edit/' . $entities->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;" data-toggle="modal" data-target="#entity-delete-modal" onclick="angular.element(this).scope().deleteEntity(' . $entities->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getEntityFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$entity = new Entity;
			$attachment = new Attachment;
			$this->data['entity_type'] = DB::table('entity_types')->find($r->entity_type_id);
			$action = 'Add';
		} else {
			$entity = Entity::withTrashed()->where('id',$id)->first();
			$attachment = Attachment::where('id', $entity->logo_id)->first();
			$this->data['entity_type'] = DB::table('entity_types')->find($entity->entity_type_id);
			$action = 'Edit';
		}
		$this->data['entity'] = $entity;
		$this->data['attachment'] = $attachment;
		$this->data['action'] = $action;
		$this->data['theme'];

		//dd($this->data);
		return response()->json($this->data);
	}

	public function saveEntity(Request $request) {
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'unique:entities,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$entity = new Entity;
				$entity->created_by_id = Auth::user()->id;
				$entity->created_at = Carbon::now();
				$entity->updated_at = NULL;
			} else {
				$entity = Entity::withTrashed()->find($request->id);
				$entity->updated_by_id = Auth::user()->id;
				$entity->updated_at = Carbon::now();
			}
			$entity->fill($request->all());
			$entity->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$entity->deleted_at = Carbon::now();
				$entity->deleted_by_id = Auth::user()->id;
			} else {
				$entity->deleted_by_id = NULL;
				$entity->deleted_at = NULL;
			}
			$entity->save();

			DB::commit();
			if (!($request->id)) {
				return response()->json([
					'success' => true,
					'message' => 'Entity Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'Entity Updated Successfully',
				]);
			}
		} catch (Exceprion $e) {
			DB::rollBack();
			return response()->json([
				'success' => false,
				'error' => $e->getMessage(),
			]);
		}
	}

	public function deleteEntity(Request $request) {
		DB::beginTransaction();
		try {
			$entity = Entity::withTrashed()->where('id', $request->id)->first();
			if (!is_null($entity->logo_id)) {
				Attachment::where('company_id', Auth::user()->company_id)->where('attachment_of_id', 20)->where('entity_id', $request->id)->forceDelete();
			}
			Entity::withTrashed()->where('id', $request->id)->forceDelete();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'Entity Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
