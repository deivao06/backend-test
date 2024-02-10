<?php

namespace App\Http\Repositories;

use App\Models\Redirect;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;

class RedirectRepository
{
    public function store($url)
    {
        $redirect = Redirect::create([ 'url' => $url ]);
        $redirect->code = Hashids::connection('main')->encode($redirect->id);
        $redirect->save();

        return $redirect;
    }

    public function update(Request $request, $code)
    {
        $url = $request->get('url');
        $status = $request->get('status');

        $redirect = Redirect::where('code', $code)->first();

        if($redirect) {
            $redirect->url = $url ?? $redirect->url;
            $redirect->status = $status ?? $redirect->status;

            $redirect->save();

            return $redirect;
        }

        return false;
    }

    public function destroy($code)
    {
        $redirect = Redirect::where('code', $code)->first();
        return $redirect->delete();
    }
}