<?php

namespace App\Http\Controllers;

use App\Http\Requests\RedirectStoreRequest;
use App\Http\Requests\RedirectUpdateRequest;
use App\Http\Resources\RedirectResource;
use App\Models\Redirect;
use App\Models\RedirectLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect as FacadesRedirect;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Redirect as LaravelRedirect;


class RedirectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return RedirectResource::collection(Redirect::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(RedirectStoreRequest $request)
    {
        $url = $request->get('url');
        $app_url = env('APP_URL', 'http://localhost');

        if (strpos($url, $app_url) !== false) {
            return response()->json(['errors' => 'The field url can not be equal application url'], 400);
        }

        $redirect = Redirect::create([ 'url' => $url ]);
        $redirect->code = Hashids::connection('main')->encode($redirect->id);
        $redirect->save();

        return new RedirectResource($redirect);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($code)
    {
        $redirect = Redirect::where('code', $code)->first();

        if(!$redirect) {
            return response()->json([ 'errors' => 'Redirect does not exists' ]);
        }

        return new RedirectResource($redirect);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(RedirectUpdateRequest $request, $code)
    {
        $url = $request->get('url');
        $status = $request->get('status');

        $redirect = Redirect::where('code', $code)->first();

        if($redirect) {
            $redirect->url = $url ?? $redirect->url;
            $redirect->status = $status ?? $redirect->status;

            $redirect->save();

            return new RedirectResource($redirect);
        }

        return response()->json([ 'errors' => 'Redirect does not exists'], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($code)
    {
        $redirect = Redirect::where('code', $code)->first();
        $redirect->delete();

        return response()->json([ 'message' => "Redirect $code deleted successfully" ], 200);
    }

    public function redirect(Request $request, $code)
    {
        $redirect = Redirect::where('code', $code)->first();

        if(!$redirect) {
            return response()->json([ 'errors' => 'Redirect does not exists' ]);
        }

        RedirectLog::create([
            'redirect_id' => $redirect->id,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'header_referer' => $request->server('HTTP_REFERER'),
            'query_params' => $request->query() ? json_encode($request->query()) : null
        ]);

        $redirect->last_access = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $redirect->save();

        $redirectQueryParams = $redirect->query_params;

        $allQueryParams = http_build_query(array_merge($request->query(), $redirectQueryParams));

        $parsedUrl = parse_url($redirect->url);

        $finalUrlToRedirect = "{$parsedUrl['scheme']}://{$parsedUrl['host']}?$allQueryParams";

        return LaravelRedirect::to($finalUrlToRedirect);
    }
}
