<?php

namespace App\Http\Controllers;

use App\Http\Repositories\RedirectRepository;
use App\Http\Requests\RedirectStoreRequest;
use App\Http\Requests\RedirectUpdateRequest;
use App\Http\Resources\RedirectResource;
use App\Models\Redirect;
use App\Models\RedirectLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Redirect as LaravelRedirect;
use Illuminate\Support\Facades\DB;

class RedirectController extends Controller
{
    private $redirectRepository;

    public function __construct()
    {
        $this->redirectRepository = new RedirectRepository;
    }

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

        $parsedUrl = parse_url($url);

        if(isset($parsedUrl['query'])) {
            $queryParams = [];
            parse_str($parsedUrl['query'], $queryParams);
            
            if(in_array("", $queryParams)) {
                return response()->json(['errors' => 'The url query params can not be empty'], 400);
            }
        }

        $redirect = $this->redirectRepository->store($url);

        if ($redirect) {
            return response()->json(['message' => "Redirect {$redirect->code} created successfuly"], 200);
        }

        return response()->json(['errors' => 'Failed to create redirect', 400]);
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

        if (!$redirect) {
            return response()->json([ 'errors' => 'Redirect does not exists' ], 400);
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
        $redirect = $this->redirectRepository->update($request, $code);
        
        if ($redirect) {
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
        $destroy = $this->redirectRepository->destroy($code);
        
        if ($destroy) {
            return response()->json([ 'message' => "Redirect deleted successfully" ], 200);
        }

        return response()->json([ 'errors' => "Failed to deleted redirect" ], 400);
    }

    public function redirect(Request $request, $code)
    {
        $redirect = Redirect::where('code', $code)->first();

        if (!$redirect) {
            return response()->json([ 'errors' => 'Redirect does not exists' ], 400);
        }

        if (!$redirect->status) {
            return response()->json([ 'errors' => 'Redirect is not active' ], 400);
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

        $allQueryParams = http_build_query(array_merge($redirect->query_params, $request->query()));

        $parsedUrl = parse_url($redirect->url);

        $finalUrlToRedirect = "{$parsedUrl['scheme']}://{$parsedUrl['host']}?$allQueryParams";

        return LaravelRedirect::to($finalUrlToRedirect);
    }

    public function stats($code)
    {
        $redirect = Redirect::where('code', $code)->first();

        if (!$redirect) {
            return response()->json([ 'errors' => 'Redirect does not exists' ], 400);
        }

        $totalHits = RedirectLog::where('redirect_id', $redirect->id)->count();

        $uniqueHits = RedirectLog::where('redirect_id', $redirect->id)->distinct('ip')->count();

        $topReferers = RedirectLog::select(DB::raw('header_referer, COUNT(*) as count'))
                        ->where('redirect_id', $redirect->id)
                        ->groupBy('header_referer')
                        ->orderByRaw('COUNT(*) DESC')
                        ->limit(3)
                        ->get();

        $last10DaysHits = RedirectLog::select(DB::raw("created_at as 'date', COUNT(*) as total, COUNT(DISTINCT(ip)) as 'unique'"))
                            ->where('redirect_id', $redirect->id)
                            ->where('created_at', '>', Carbon::now()->subDay(10))
                            ->groupBy('created_at')
                            ->orderBy('created_at')
                            ->get();


        return response()->json([
            'total_accesses' => $totalHits,
            'total_unique' => $uniqueHits,
            'top_referrers' => $topReferers,
            'last_10_days' => $last10DaysHits
        ], 200);
    }
}
