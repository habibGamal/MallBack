<?php

namespace App\Http\Controllers;

use App\Http\Requests\DifferentBranchesRequest;
use App\Http\Requests\SameBranchesRequest;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BranchController extends Controller
{
    private $storeOnly = [
        'store_name' => 'required|string|max:255',
        'address' => 'required|string',
        'logo' => 'required|mimes:png,jpg,jpeg,webp|max:1024',
        'logo_position' => 'string|nullable',
    ];

    public function __construct()
    {
        $this->middleware('auth:admin')->except('index', 'show');
    }
    private function sameBranchesValidation($branches_number)
    {
        return [
            'store_name' => 'required|string|max:255',
            'short_branch_names' => "required|array|size:{$branches_number}",
            'short_branch_names.*' => 'required|string',
            'addresses' => "required|array|size:{$branches_number}",
            'addresses.*' => 'required|string',
            'logo' => 'required|mimes:png,jpg,jpeg,webp|max:1024',
            'logo_position' => 'string|nullable',
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Branch::all();
    }

    /**
     * create store with ONLY one branch
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $storeId
     * @return \Illuminate\Http\Response
     */
    private function createStoreOnly(Request $request, $storeId)
    {
        $request->validate($this->storeOnly);
        // => hanlde logo
        $logoFile = $request->file('logo');
        // => get the position the logo
        $position = $request->input('logo_position');
        $jsonLogo = savePhotos([$logoFile], [$position]);
        $branch = Branch::create([
            'name' => $request->input('store_name'),
            'short_name' => $request->input('store_name'),
            'address' => $request->input('address'),
            'gps' => 'gps location',
            'logo' => $jsonLogo,
            'store_id' => $storeId,
        ]);
        return $branch;
    }

    /**
     * create store with branches have diffrent names and logo
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $storeId
     * @return \Illuminate\Http\Response
     */
    private function createStoreWithDifferentBranches(DifferentBranchesRequest $request, $storeId)
    {
        // => get logos and positions arrays to save them
        $logos = $request->file('logos');
        $positions = $request->logos_position;
        // => buffer data
        $data = [];
        for ($i = 0; $i < $request->branches_number; $i++) {
            // => save logo and its position and get it as json [[path,position]]
            $jsonLogo = savePhotos([$logos[$i]], [$positions[$i]]);
            $data[] = [
                'name' => $request->branch_names[$i],
                'short_name' => $request->branch_names[$i],
                'address' => $request->addresses[$i],
                'gps' => 'gps location',
                'logo' => $jsonLogo,
                'store_id' => $storeId,
            ];
        }

        $branchs = Branch::upsert($data, ['name', 'short_name', 'address', 'gps', 'logo', 'store_id']);
        return $branchs;
    }

    /**
     * create store with branches have same names and logo
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $storeId
     * @return \Illuminate\Http\Response
     */
    private function createStoreWithSameBranches(SameBranchesRequest $request, $storeId)
    {
        $branches_number = $request->branches_number;
        $request->validate($this->sameBranchesValidation($branches_number));
        // => hanlde logo
        $logoFile = $request->file('logo');
        $position = $request->input('logo_position');
        $jsonLogo = savePhotos([$logoFile], [$position]);
        // => buffer data
        $data = [];
        for ($i = 0; $i < $request->branches_number; $i++) {
            $data[] = [
                'name' => $request->store_name,
                'short_name' => $request->short_branch_names[$i],
                'address' => $request->addresses[$i],
                'gps' => 'gps location',
                'logo' => $jsonLogo,
                'store_id' => $storeId,
            ];
        }

        $branchs = Branch::upsert($data, ['name', 'short_name', 'address', 'gps', 'logo', 'store_id']);
        return $branchs;
    }

    /**
     * create store with branches have same names and logo
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $storeId
     * @return \Illuminate\Http\Response
     */
    private function createStoreWithBranches(Request $request, $storeId)
    {
        $request->validate([
            'branches' => ['required', 'boolean', Rule::in([true, 1, '1'])],
            'same_branches' => 'required|bool',
            'branches_number' => 'required|numeric|integer',
        ]);
        $request = $request->input('same_branches') ? App::make(SameBranchesRequest::class) : App::make(DifferentBranchesRequest::class);
        return $request->input('same_branches')
            ? $this->createStoreWithSameBranches($request, $storeId)
            : $this->createStoreWithDifferentBranches($request, $storeId);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $storeId = $request->user('admin')->store->id;
        $request->validate([
            'branches' => ['required', 'boolean'],
        ]);
        return $request->input('branches')
            ? $this->createStoreWithBranches($request, $storeId)
            : $this->createStoreOnly($request, $storeId);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $branch = Branch::findOrFail($id);
        $productsOfBranch = $branch->products()->get(['id', 'pictures', 'name', 'price', 'offer_price']);
        $returnedStructure = [
            'branch' => $branch,
            'products' => $productsOfBranch
        ];
        return $returnedStructure;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
