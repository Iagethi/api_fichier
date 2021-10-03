<?php

namespace App\Http\Controllers;

use App\Models\Ressource;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RessourceController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->user
            ->ressources()
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request, $id)
    {
        dd($request->name);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'source' => 'required|mimes:png,jpg,jpeg,csv,txt,xlx,xls,pdf|max:2048',
        ]);

        if ($validator->fails()) {

            return response()->json(['error' => $validator->errors()], 401);
        }

        $file = new Ressource();

        if ($request->file()) {
            $name = time() . '_' . $request->source->getClientOriginalName();
            $filePath = $request->file('source')->storeAs('uploads', $name, 'public');

            //store your file into directory and db
            $file->source = '/storage/' . $filePath;
            $file->name = $request->name;
            $file->user_id = $id;
            $file->save();

            return response()->json([
                "success" => true,
                "message" => "File successfully uploaded",
                "file" => $request->name
            ], 201);
        }

        return response()->json([
            "success" => false,
            "message" => "File not uploaded.",
        ], 400);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Ressource  $ressource
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $files = Ressource::where('user_id', $id)->get();

        foreach ($files as $file) {
            $file->name;
        }

        if (count($files) <= 0) {
            return response()->json([
                "success" => false,
                "message" => "File not found."
            ], 404);
        }

        return response()->json([
            "success" => true,
            "message" => "File retrieved successfully.",
            "data" => $files
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ressource  $ressource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //Validate data
        $data = $request->only('name', 'source');
        $validator = Validator::make($data, [
            'name' => 'string',
            'source' => 'mimes:png,jpg,jpeg,csv,txt,xlx,xls,pdf|max:2048',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        $file = Ressource::find($id);

        if (is_null($file)) {
            return response()->json([
                "success" => false,
                "message" => "File not found."
            ], 404);
        }

        $fileToDelete = $file->source;

        if (isset($request->name) || isset($request->source)) {
            if ($request->file()) {
                $fileToDelete = substr($fileToDelete, 9);
                Storage::disk('public')->delete($fileToDelete);
                dd('la');
                $name = time() . '_' . $request->source->getClientOriginalName();
                $filePath = $request->file('source')->storeAs('uploads', $name, 'public');
                $file->source = '/storage/' . $filePath;
                $file->update(array('source' => $filePath));
            }
            if (isset($request->name)) {
                $file->update(array('name' => $request->name));
            }
        }

        //ressource updated, return success response
        return response()->json([
            'success' => true,
            'message' => 'File updated successfully',
            'data' => $file
        ], 200);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ressource  $ressource
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $file = Ressource::find($id);

        if (is_null($file)) {
            return response()->json([
                "success" => false,
                "message" => "File not found."
            ], 404);
        }

        $filePath = substr($file->source, 9);
        Storage::disk('public')->delete($filePath);
        $file->delete();


        return response()->json([
            "success" => true,
            "message" => "File deleted."
        ], 204);
    }
}
