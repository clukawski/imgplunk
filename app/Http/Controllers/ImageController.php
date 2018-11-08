<?php

namespace App\Http\Controllers;

use DB;
use URL;
use Validator;
use Storage;

use App\Image;
use App\Tag;
use App\ImageTag;

use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ImageController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Upload and store image & tags
     *
     * @param Request $request Laravel request object
     * @return string JSON formatted response
     *
     */
    public function store(Request $request)
    {
        // Validate tags are included/is valid image/10MiB or less
        $validator = Validator::make(
            $request->all(),
            [
                'image' => 'required|file|image|max:10240',
                'tags' => 'required|json',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Validate tags json (alpha-numeric only)
        $tags = json_decode($request->input('tags'));
        $validator = Validator::make(
            $tags->tags,
            [
                '*' => 'alpha_num',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get sha256 hash of image to use as reference
        $imageFile = $request->file('image');
        $fileHash = hash_file('sha256', $imageFile->path());

        // Store image and image hash
        $image = Image::firstOrNew(['hash' => $fileHash, 'ext' => $imageFile->getClientOriginalExtension()]);
        Storage::putFileAs("public", $imageFile, $fileHash.".".$imageFile->getClientOriginalExtension());
        $image->save();

        // Create and collect tag entries if they don't already exist
        $tagEntries = $tags->tags;
        foreach ($tagEntries as $tag) {
            $tag = Tag::firstOrNew(['tag' => $tag]);
            $tag->save();

            $imageTag = ImageTag::firstOrNew(['tag_id' => $tag->id, 'image_id' => $image->id]);
            $imageTag->save();
        }

        return response()->json([
            'url' => URL::to('/').Storage::url($image->hash.".".$image->ext),
            'tags' => $tagEntries
        ]);
    }

    /**
     * Fetch images by matching tags
     *
     * @param Request $request Laravel request object
     * @return string JSON formatted response
     *
     */
    function tags(Request $request) {
        // Validate tags are included/is valid image/10MiB or less
        $validator = Validator::make(
            $request->all(),
            [
                'tags' => 'required|json',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Validate tags json (alpha-numeric only)
        $tags = json_decode($request->input('tags'));
        $validator = Validator::make(
            $tags->tags,
            [
                '*' => 'alpha_num',
            ]
        );

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Respond appropriately if we don't have any matching tags
        $imageTagIds = DB::table('tags')->whereIn('tag', $tags->tags)->pluck('id')->toArray();
        if (empty($imageTagIds)) {
            return response()->json(
                [
                    'tags' => 'No matching tags found'
                ],
                404
            );
        }

        // Also respond appropriately if we don't have any images matching those specific tags
        $imageIds = DB::table('image_tags')->whereIn('tag_id', $imageTagIds)->pluck('image_id')->toArray();
        if (empty($imageIds)) {
            return response()->json(
                [
                    'tags' => 'No images matching tags found'
                ],
                404
            );
        }

        // Fetch matching images
        $images = DB::table('images')->whereIn('id', $imageIds)->get();
        $imageUrls = [];
        foreach ($images as $image) {
            $imageUrls[] = URL::to('/').Storage::url($image->hash.".".$image->ext);
        }

        // Build response json
        return response()->json([
            'urls' => $imageUrls,
            'tags' => $tags->tags
        ]);
    }
}
