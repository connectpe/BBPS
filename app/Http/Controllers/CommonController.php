<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{
    public function fetchData(Request $request, $type, $id = 0, $returnType = 'all')
    {
        // dd($this->authrize('isUser'));

        $request['return'] = 'all';
        $request->orderIdArray = [];
        $request->serviceIdArray = [];
        $request->userIdArray = [];
        $request->adminUserIdArray = [];
        $request['returnType'] = $returnType;
        $parentData = session('parentData');
        $request['where'] = 0;
        $request['type'] = $type;

        switch ($type) {

            case 'users':
                $request['table'] = '\App\Models\User';
                $request['searchData'] = ['id', 'name', 'email', 'mobile', 'created_at'];   // it is for the Datatable search box
                $request['select'] = 'all';
                $request['with'] = ['business'];

                $orderIndex = $request->get('order');

                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column'];
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir'];
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC';
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }

                $request['whereIn'] = 'id';
                $request['parentData'] = [$request->id];

                if (Auth::user()->role_id == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }

                break;

            case 'global-service':
                $request['table'] = '\App\Models\GlobalService';
                $request['searchData'] = ['id', 'service_name', 'created_at'];
                $request['select'] = 'all';
                // $request['with'] = ['business'];

                $orderIndex = $request->get('order');

                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column'];
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir'];
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC';
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }

                $request['whereIn'] = 'id';
                $request['parentData'] = [$request->id];

                if (Auth::user()->role_id == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }

                break;

                $request['table'] = '\App\Models\Insurance';
                $request['searchData'] = ['name', 'email', 'mobile', 'pan', 'agentId', 'status', 'created_at'];
                $request['select'] = 'all';
                $request['with'] = ['user'];
                if (! isset($request['from']) && empty($request['from'])) {
                    $request['from'] = date('Y-m-d');
                }
                if (! isset($request['to']) && empty($request['to'])) {
                    $request['to'] = date('Y-m-d');
                }
                $orderIndex = $request->get('order');

                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column']; // Column index
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir']; // asc or desc
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC'; // asc or desc
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }

                if (Auth::user()->is_admin == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }
                break;

            case 'transactions':
                $request['table'] = '\App\Models\Transaction';

                $request['searchData'] = ['id',  'created_at', 'reference_number', 'user_id', 'operator_id', 'circle_id', 'status', 'amount', 'transaction_type'];

                $request['select'] = 'all';
                $request['with'] = ['user', 'operator', 'circle'];
                $orderIndex = $request->get('order');
                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column'];
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir'];
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC';
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }
                $request['whereIn'] = 'id';
                $request['parentData'] = [$request->id];
                if (Auth::user()->role_id == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }
                break;
            case 'serviceRequest':
                $request['table'] = '\App\Models\UserService';
                $request['searchData'] = ['user_id', 'status', 'service_id', 'transaction_amount', 'created_at'];
                $request['select'] = 'all';
                $request['with'] = ['service'];
                $orderIndex = $request->get('order');
                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column'];
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir'];
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC';
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }
                $request['whereIn'] = 'id';
                $request['parentData'] = [$request->id];
                if (Auth::user()->role_id == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }
                break;

            case 'providers':
                $request['table'] = '\App\Models\Provider';
                $request['searchData'] = ['provider_name', 'provider_slug', 'service_id', 'created_at'];
                $request['select'] = 'all';
                $request['with'] = ['service', 'updatedBy'];

                $orderIndex = $request->get('order');
                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column'];
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir'];
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC';
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }
                $request['whereIn'] = 'id';
                $request['parentData'] = [$request->id];
                if (Auth::user()->role_id == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }
                break;

            case 'api-logs':
                $request['table'] = '\App\Models\ApiLog';
                $request['searchData'] = ['user_id', 'method', 'endpoint', 'request_body', 'response_body', 'status_code', 'ip_address', 'user_agent', 'execution_time', 'created_at'];
                $request['select'] = 'all';
                $request['with'] = ['user', 'user.business'];

                $orderIndex = $request->get('order');
                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column'];
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir'];
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC';
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }
                $request['whereIn'] = 'id';
                $request['parentData'] = [$request->id];
                if (Auth::user()->role_id == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }
                break;

            case 'enabled-services':
                $request['table'] = '\App\Models\UserService';
                $request['searchData'] = ['user_id', 'transaction_amount', 'is_active', 'is_api_enable', 'service_id', 'user_id'];
                $request['select'] = 'all';
                $request['with'] = ['user', 'user.business', 'service'];

                $orderIndex = $request->get('order');
                if (isset($orderIndex) && count($orderIndex)) {
                    $columnsIndex = $request->get('columns');
                    $columnIndex = $orderIndex[0]['column'];
                    $columnName = $columnsIndex[$columnIndex]['data'];
                    $columnSortOrder = $orderIndex[0]['dir'];
                    if ($columnName == 'new_created_at') {
                        $columnName = 'created_at';
                    }
                    if ($columnName == '0') {
                        $columnName = 'created_at';
                        $columnSortOrder = 'DESC';
                    }
                    $request['order'] = [$columnName, $columnSortOrder];
                } else {
                    $request['order'] = ['id', 'DESC'];
                }
                $request['whereIn'] = 'id';
                $request['parentData'] = [$request->id];
                if (Auth::user()->role_id == '1') {
                    $request['parentData'] = 'all';
                } else {
                    $request['whereIn'] = 'user_id';
                    $request['parentData'] = [Auth::user()->id];
                }
            case 'schemes':
                $request['table'] = '\App\Models\Scheme'; // Ensure your model path is correct
                $request['searchData'] = ['id', 'scheme_name', 'created_at'];
                $request['select'] = 'all';
                $request['order'] = ['id', 'DESC'];
                $request['parentData'] = 'all';
                break;

           
            case 'scheme-relations':
                $request['table'] = '\App\Models\UserConfig'; 
                $request['searchData'] = ['id'];
                $request['select'] = 'all';
                $request['with'] = ['user', 'scheme'];
                $request['order'] = ['id', 'DESC'];
                $filterColumnsMap['scheme-relations'] = ['user_id', 'scheme_id'];
                break;
        }

        // For filter the Records
        $filterColumnsMap = [
            'users' => ['id', 'email', 'mobile', 'status'],
            'global-service' => ['service_name', 'status'],
            'insurance' => ['name', 'email', 'mobile', 'pan', 'agentId', 'status'],
            'transactions' => ['reference_number', 'user_id', 'operator_id', 'circle_id', 'status', 'amount', 'transaction_type'],
            'serviceRequest' => ['status', 'service_id'],
            'providers' => ['status', 'service_id'],
            'api-logs' => ['status', 'user_id'],
            'enabled-services' => ['service_id', 'user_id'],
			 'scheme-relations' => ['user_id', 'scheme_id'],
            // add more types and columns here
        ];

        $filters = []; // separate variable to store dynamic filters

        if (isset($filterColumnsMap[$type])) {
            foreach ($filterColumnsMap[$type] as $column) {
                if ($request->has($column) && $request->$column !== '') {
                    $filters[$column] = $request->$column;
                }
            }
        }

        $request->merge(['filters' => $filters]);

        try {
            $totalData = $this->getData($request, 'count');
        } catch (\Exception $e) {
            $totalData = 0;
        }
        if (isset($request->search['value'])) {
            $request->searchText = $request->search['value'];
        }
        if (isset($request->userId)) {
            $request->adminUserIdArray = $request->userId;
        }
        if (isset($request->searchText) && ! empty($request->searchText) && $type == 'orders') {
            $getOrderRefId = self::getOrderRefId($request->searchText);
            $request->orderIdArray = $getOrderRefId;
        }

        // if (isset($request->searchText) && !empty($request->searchText) && $type == 'serviceRequest') {
        // 	$getServiceId = self::getServiceId($request->searchText);
        // 	$request->serviceIdArray = $getServiceId;
        // }

        if (isset($request->searchText) && ! empty($request->searchText) && in_array($type, ['bulkpayouts', 'serviceRequest']) && \Auth::user()->is_admin == '1') {
            $getUserId = self::getUserId($request->searchText);
            $request->userIdArray = $getUserId;
        }

        if (
            (isset($request->searchText) && ! empty($request->searchText)) ||
            (isset($request->to) && ! empty($request->to)) ||
            (isset($request->tr_type) && ! empty($request->tr_type)) ||
            (isset($request->account_number) && ! empty($request->account_number)) ||
            (isset($request->from) && ! empty($request->from)) ||
            (isset($request->date_from) && ! empty($request->date_from)) ||
            (isset($request->date_to) && ! empty($request->date_to)) ||
            (isset($request->status) && $request->status != '') ||
            (isset($request->apes_status_array) && $request->apes_status_array != '') ||
            (isset($request->area) && $request->area != '') ||
            (isset($request->account_type) && $request->account_type != '') ||
            (isset($request->tr_identifiers) && $request->tr_identifiers != '') ||
            (isset($request->service_id_array) && $request->service_id_array != '') ||
            (isset($request->integration_id) && $request->integration_id != '') ||
            (isset($request->transaction_type_array) && $request->transaction_type_array != '') ||
            (isset($request->route_type_array) && $request->route_type_array != '') ||
            (isset($request->is_active) && $request->is_active != '') ||
            (isset($request->userId) && $request->userId != '') ||
            (isset($request->user_id) && ! empty($request->user_id)) ||
            (isset($request->service_type) && ! empty($request->service_type))
        ) {
            $request['where'] = 1;
        }

        try {
            $totalFiltered = $this->getData($request, 'count');
        } catch (\Exception $e) {
            $totalFiltered = 0;
        }

        try {
            $data = $this->getData($request, 'data');
        } catch (\Exception $e) {
            print_r($e->getMessage());
            $data = [];
        }
        if ($request->return == 'all' || $returnType == 'all') {
            $json_data = [
                'draw' => intval($request['draw']),
                'recordsTotal' => intval($totalData),
                'recordsFiltered' => intval($totalFiltered),
                'data' => $data,
            ];
            echo json_encode($json_data);
        } else {
            return response()->json($data);
        }
    }

    protected function getData($request, $type = 'data')
    {

        $model = $request['table'];

        // Start query
        $query = $model::query();

        if (isset($request['whereIn']) && isset($request['parentData'])) {
            if ($request['parentData'] !== 'all') {
                $query->whereIn($request['whereIn'], (array) $request['parentData']);
            }
        }

        if ($request->has('filters') && ! empty($request->filters)) {
            foreach ($request->filters as $column => $value) {

                if ($value === null || $value === '') {
                    continue;
                }

                if (is_numeric($value)) {
                    $query->where($column, $value);
                } else {
                    $query->where($column, 'LIKE', '%'.$value.'%');
                }
            }
        }

        if (isset($request['where']) && $request['where'] == 1 && isset($request->searchText) && ! empty($request->searchText)) {
            $query->where(function ($q) use ($request) {
                foreach ($request['searchData'] as $column) {
                    $q->orWhere($column, 'LIKE', '%'.$request->searchText.'%');
                }
            });
        }

        if (! empty($request['from']) && ! empty($request['to'])) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [
                $request['from'],
                $request['to'],
            ]);
        }
        if (! empty($request->get('date_from')) && ! empty($request->get('date_to'))) {
            $query->whereBetween(DB::raw('DATE(created_at)'), [
                $request->get('date_from'),
                $request->get('date_to'),
            ]);
        } elseif (! empty($request->get('date_from'))) {
            $query->whereDate('created_at', '>=', $request->get('date_from'));
        } elseif (! empty($request->get('date_to'))) {
            $query->whereDate('created_at', '<=', $request->get('date_to'));
        }

        if ($type === 'count') {
            return $query->count();
        }

        if (isset($request['order'])) {
            $query->orderBy($request['order'][0], $request['order'][1]);
        }

        if (isset($request['length']) && $request['length'] != -1) {
            $query->offset($request['start'])->limit($request['length']);
        }

        if (isset($request['with'])) {
            $query->with($request['with']);
        }

        if (isset($request['select']) && $request['select'] !== 'all') {
            $query->select($request['select']);
        }

        return $query->get();
    }
    /**
     * Undocumented function
     *
     * @param [type] $request
     * @param [type] $returnType
     * @return void
     */
}
