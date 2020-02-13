<?php

namespace Abs\EntityPkg;
use Abs\Basic\Attachment;
use Abs\EntityPkg\Entity;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use File;
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

	public function getEntityList(Request $request) {
		$entities = Entity::withTrashed()
			->select([
				'entities.*',
				DB::raw('IF(entities.deleted_at IS NULL, "Active","Inactive") as status'),
			])
			->where('entities.company_id', $this->company_id)
		/*->where(function ($query) use ($request) {
				if (!empty($request->question)) {
					$query->where('entities.question', 'LIKE', '%' . $request->question . '%');
				}
			})*/
			->orderby('entities.id', 'desc');

		return Datatables::of($entities)
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
			$action = 'Add';
		} else {
			$entity = Entity::withTrashed()->find($id);
			$attachment = Attachment::where('id', $entity->logo_id)->first();
			$action = 'Edit';
		}
		$this->data['entity'] = $entity;
		$this->data['attachment'] = $attachment;
		$this->data['action'] = $action;
		$this->data['theme'];

		return response()->json($this->data);
	}

	public function saveEntity(Request $request) {
		//dd($request->all());
		try {
			$error_messages = [
				'name.required' => 'Name is Required',
				'name.unique' => 'Name is already taken',
				'delivery_time.required' => 'Delivery Time is Required',
				'charge.required' => 'Charge is Required',
			];
			$validator = Validator::make($request->all(), [
				'name' => [
					'required:true',
					'unique:entities,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
				],
				'delivery_time' => 'required',
				'charge' => 'required',
				'logo_id' => 'mimes:jpeg,jpg,png,gif,ico,bmp,svg|nullable|max:10000',
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

			if (!empty($request->logo_id)) {
				if (!File::exists(public_path() . '/themes/' . config('custom.admin_theme') . '/img/entity_logo')) {
					File::makeDirectory(public_path() . '/themes/' . config('custom.admin_theme') . '/img/entity_logo', 0777, true);
				}

				$attacement = $request->logo_id;
				$remove_previous_attachment = Attachment::where([
					'entity_id' => $request->id,
					'attachment_of_id' => 20,
				])->first();
				if (!empty($remove_previous_attachment)) {
					$remove = $remove_previous_attachment->forceDelete();
					$img_path = public_path() . '/themes/' . config('custom.admin_theme') . '/img/entity_logo/' . $remove_previous_attachment->name;
					if (File::exists($img_path)) {
						File::delete($img_path);
					}
				}
				$random_file_name = $entity->id . '_entity_file_' . rand(0, 1000) . '.';
				$extension = $attacement->getClientOriginalExtension();
				$attacement->move(public_path() . '/themes/' . config('custom.admin_theme') . '/img/entity_logo', $random_file_name . $extension);

				$attachment = new Attachment;
				$attachment->company_id = Auth::user()->company_id;
				$attachment->attachment_of_id = 20; //User
				$attachment->attachment_type_id = 40; //Primary
				$attachment->entity_id = $entity->id;
				$attachment->name = $random_file_name . $extension;
				$attachment->save();
				$entity->logo_id = $attachment->id;
				$entity->save();
			}

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
