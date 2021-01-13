<?php
declare(strict_types=1);

namespace App\Controller\System;

use Hyperf\DbConnection\Db;

class GalleryController extends BaseController
{
    public function originLists(): array
    {
        $validate = $this->curd->originListsValidation();
        if ($validate->fails()) {
            return [
                'error' => 1,
                'msg' => $validate->errors()
            ];
        }

        return $this->curd
            ->originListsModel('gallery')
            ->setOrder('create_time', 'desc')
            ->result();
    }

    public function lists(): array
    {
        $validate = $this->curd->listsValidation();
        if ($validate->fails()) {
            return [
                'error' => 1,
                'msg' => $validate->errors()
            ];
        }

        return $this->curd
            ->listsModel('gallery')
            ->setOrder('create_time', 'desc')
            ->result();
    }

    public function bulkAdd(): array
    {
        $body = $this->request->post();
        $validate = $this->validation->make($body, [
            'type_id' => 'required',
            'data' => 'required|array',
            'data.*.name' => 'required',
            'data.*.url' => 'required'
        ]);
        if ($validate->fails()) {
            return [
                'error' => 1,
                'msg' => $validate->errors()
            ];
        }
        $data = [];
        $now = time();
        foreach ($body['data'] as $value) {
            $data[] = [
                'type_id' => $body['type_id'],
                'name' => $value['name'],
                'url' => $value['url'],
                'create_time' => $now,
                'update_time' => $now
            ];
        }
        Db::table('gallery')->insert($data);
        return [
            'error' => 0,
            'msg' => 'ok'
        ];
    }

    public function edit(): array
    {
        $validate = $this->curd->editValidation();
        if ($validate->fails()) {
            return [
                'error' => 1,
                'msg' => $validate->errors()
            ];
        }
        return $this->curd
            ->editModel('gallery')
            ->result();
    }

    public function bulkEdit(): array
    {
        $body = $this->request->post();
        $validate = $this->validation->make($body, [
            'type_id' => 'required',
            'ids' => 'required|array',
        ]);
        if ($validate->fails()) {
            return [
                'error' => 1,
                'msg' => $validate->errors()
            ];
        }
        Db::transaction(function () use ($body) {
            foreach ($body['ids'] as $value) {
                Db::table('gallery')
                    ->where('id', '=', $value)
                    ->update(['type_id' => $body['type_id']]);
            }
        });
        return [
            'error' => 0,
            'msg' => 'ok'
        ];
    }

    public function delete(): array
    {
        $validate = $this->curd->deleteValidation();
        if ($validate->fails()) {
            return [
                'error' => 1,
                'msg' => $validate->errors()
            ];
        }

        return $this->curd
            ->deleteModel('gallery')
            ->result();
    }

    public function count(): array
    {
        $total = Db::table('gallery')->count();
        $values = Db::table('gallery')
            ->groupBy(['type_id'])
            ->get(['type_id', Db::raw('count(*) as size')]);

        return [
            'error' => 0,
            'data' => [
                'total' => $total,
                'values' => $values
            ]
        ];
    }
}