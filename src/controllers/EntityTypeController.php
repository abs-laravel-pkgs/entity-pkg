<?php

namespace Abs\EntityPkg;
use Abs\Basic\Attachment;
use Abs\EntityPkg\EntityType;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class EntityTypeController extends Controller {

	private $company_id;
	public function __construct() {
		$this->data['theme'] = config('custom.admin_theme');
		$this->company_id = config('custom.company_id');
	}

	public function getEntityTypes(Request $request) {
		$this->data['entity_types'] = EntityType::
			select([
			'entity_types.question',
			'entity_types.answer',
		])
			->where('entity_types.company_id', $this->company_id)
			->orderby('entity_types.display_order', 'asc')
			->get()
		;
		$this->data['success'] = true;

		return response()->json($this->data);

	}

	public function getEntityTypeList(Request $r) {
		$entity_types = EntityType::withTrashed()
			->select([
				'entity_types.id',
				'entity_types.name',
				DB::raw('IF(entity_types.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('entity_types.company_id', $this->company_id)
			->where('entity_types.entity_type_id', $r->entity_type_id)

		/*->where(function ($query) use ($request) {
				if (!empty($request->question)) {
					$query->where('entity_types.question', 'LIKE', '%' . $request->question . '%');
				}
			})*/
			->orderby('entity_types.id', 'desc');

		return Datatables::of($entity_types)
			->addColumn('name', function ($entity_types) {
				$status = $entity_types->status == 'Active' ? 'green' : 'red';
				return '<span class="status-indicator ' . $status . '"></span>' . $entity_types->name;
			})
			->addColumn('action', function ($entity_types) {
				$img1 = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow.svg');
				$img1_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/edit-yellow-active.svg');
				$img_delete = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-default.svg');
				$img_delete_active = asset('public/themes/' . $this->data['theme'] . '/img/content/table/delete-active.svg');
				$output = '';
				$output .= '<a href="#!/entity-pkg/entity/edit/' . $entity_types->id . '" id = "" ><img src="' . $img1 . '" alt="Edit" class="img-responsive" onmouseover=this.src="' . $img1_active . '" onmouseout=this.src="' . $img1 . '"></a>
					<a href="javascript:;" data-toggle="modal" data-target="#entity-delete-modal" onclick="angular.element(this).scope().deleteEntityType(' . $entity_types->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete" onmouseover=this.src="' . $img_delete_active . '" onmouseout=this.src="' . $img_delete . '"></a>
					';
				return $output;
			})
			->make(true);
	}

	public function getEntityTypeFormData(Request $r) {
		$id = $r->id;
		if (!$id) {
			$entity = new EntityType;
			$attachment = new Attachment;
			$action = 'Add';
		} else {
			$entity = EntityType::withTrashed()->find($id);
			$attachment = Attachment::where('id', $entity->logo_id)->first();
			$action = 'Edit';
		}
		$this->data['entity'] = $entity;
		$this->data['attachment'] = $attachment;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveEntityType(Request $request) {
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'unique:entity_types,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
			], $error_messages);
			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			DB::beginTransaction();
			if (!$request->id) {
				$entity = new EntityType;
				$entity->created_by_id = Auth::user()->id;
				$entity->created_at = Carbon::now();
				$entity->updated_at = NULL;
			} else {
				$entity = EntityType::withTrashed()->find($request->id);
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
					'message' => 'EntityType Added Successfully',
				]);
			} else {
				return response()->json([
					'success' => true,
					'message' => 'EntityType Updated Successfully',
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

	public function deleteEntityType(Request $request) {
		DB::beginTransaction();
		try {
			$entity = EntityType::withTrashed()->where('id', $request->id)->first();
			if (!is_null($entity->logo_id)) {
				Attachment::where('company_id', Auth::user()->company_id)->where('attachment_of_id', 20)->where('entity_id', $request->id)->forceDelete();
			}
			EntityType::withTrashed()->where('id', $request->id)->forceDelete();

			DB::commit();
			return response()->json(['success' => true, 'message' => 'EntityType Deleted Successfully']);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}
}
