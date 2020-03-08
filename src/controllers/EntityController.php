<?php

namespace Abs\EntityPkg;
//use Abs\BasicPkg\Attachment;
use Abs\EntityPkg\Entity;
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

	public function getEntityList(Request $r) {
		$this->company_id = 1;
		$entities = Entity::withTrashed()
			->select([
				'entities.id as id',
				'entities.name',
				'entities.entity_type_id',
				DB::raw('IF(entities.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('entities.company_id', $this->company_id)
			->where('entities.entity_type_id', $r->entity_type_id)

			->where(function ($query) use ($r) {
				if (!empty($r->entity_name)) {
					$query->where('entities.name', 'LIKE', '%' . $r->entity_name . '%');
				}
			})
			->where(function ($query) use ($r) {
				if (!empty($r->status_id)) {
					if ($r->status_id == 1) {
						$query->whereNull('entities.deleted_at');
					} else {
						$query->whereNotNull('entities.deleted_at');
					}
				}
			})
			->orderby('entities.id', 'desc');

		return Datatables::of($entities)
			->rawColumns(['action', 'name'])
			->addColumn('name', function ($entities) {
				$status = $entities->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $entities->name;
			})
			->addColumn('action', function ($entities) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a title="Edit" href="#!/entity-pkg/entity/edit/' . $entities->entity_type_id . '/' . $entities->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;" data-toggle="modal" data-target="#alert-modal-red" onclick="angular.element(this).scope().deleteEntity(' . $entities->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getEntityFormData(Request $r) {
		//dd($r->all());
		$id = isset($r->id) ? $r->id : NULL;
		if (!$id) {
			$entity = new Entity;
			//$attachment = new Attachment;
			$action = 'Add';
		} else {
			$entity = Entity::withTrashed()->find($id);
			$action = 'Edit';
		}
		$this->data['entity'] = $entity;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveEntity(Request $request) {
		//dd('save',$request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'unique:entities,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id . ',entity_type_id,' . $request->entity_type_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$entity = new Entity;
				$entity->created_by = Auth::user()->id;
				$entity->created_at = Carbon::now();
				$entity->updated_at = NULL;
			} else {
				$entity = Entity::withTrashed()->find($request->id);
				$entity->updated_by = Auth::user()->id;
				$entity->updated_at = Carbon::now();
			}
			$entity->fill($request->all());
			$entity->company_id = Auth::user()->company_id;
			if ($request->status == 'Inactive') {
				$entity->deleted_at = Carbon::now();
				$entity->deleted_by = Auth::user()->id;
			} else {
				$entity->deleted_by = NULL;
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
		//dd($request->id);
		DB::beginTransaction();
		try {
			$entity = Entity::withTrashed()->where('id', $request->id)->first();
			if (!is_null($entity->logo_id)) {
				Attachment::where('company_id', Auth::user()->company_id)->where('attachment_of_id', 20)->where('entity_id', $request->id)->forceDelete();
			}
			Entity::withTrashed()->where('id', $request->id)->forceDelete();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'JV Rejection Reason Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
