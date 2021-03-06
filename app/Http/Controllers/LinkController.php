<?php

namespace App\Http\Controllers;

use App\Http\Components\Message;
use App\Models\Link;
use App\Models\Tag;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LinkController extends Controller
{
    use Message;
    public function listIndex($message=null){
        $columns=[];
        $query = "SHOW COLUMNS FROM links";
        $results = DB::select($query);
        foreach($results as $result)
            array_push($columns,$result->Field);
        $links = Link::latest()->paginate(10);
        
        if($message){
            return view('link.list',compact('links','columns'))->with('message','New People added ...');
        }else{
            return view('link.list',compact('links','columns'));
        }
    }

    public function linkEdit(Link $link){
        dd($link);
    }

    public function showUser(User $user){
        dd($user);
    }

    public function selectAllParents(Tag $tag){
        // link:$('#link').val(),
        // tags:$('#tag').val()

        $parents = [];
        $current = $tag;
        while($current->parent){
            $parents[] = $current->toArray();
            $current = $current->parent;
        }
        if($current)
            $parents[] = $current->toArray();
            
        $this->apiSuccess();
        $this->data = $parents;
        return $this->apiOutput(Response::HTTP_OK, "Parent tags got successfully");
    }

    public function tagUpdate(Tag $tag,Request $request){
        if($request->tags){
            $all_numeric = true;

            foreach ($request->tags as $key) { 
                if (!(is_numeric($key))) {
                    $all_numeric = false;
                    break;
                } 
            }

            if ($all_numeric) {
                $request_tags = ( is_array($request->tags) ? $request->tags : explode(',', $request->tags) ) ?? [] ;
                $tags =Tag::whereIn('id',$request_tags)->update(['parent_id'=>$tag->id]);
                $this->apiSuccess();
                return $this->apiOutput(Response::HTTP_OK,"Child Tags Updated ...");
            } 
            else {
                return $this->apiOutput(Response::HTTP_OK,"Adding new tags are not allowed here, added them in link entry ...");
            }
        }else{
            // empty from all child tags
            $tags =Tag::where('parent_id',$tag->id)->update(['parent_id' => null]);
            $this->apiSuccess();
            return $this->apiOutput(Response::HTTP_OK,"Removed as parent from all child tags ...");
        }
        
        
        

        

        
    }

    public function tagEditPage(Tag $tag){
        $tags = Tag::all();
        return view('link.tags.edit',compact('tags','tag'));
    }

    public function tagsIndex(){
        $tags = Tag::all();
        return view('link.tags.index',compact('tags'));
    }

    public function checkUniqueLink(Request $request){
        
        $link = Link::where('link',$request->link);

        $check_unique = false;
        if($link->count() > 0){
            $check_unique = true;
            $link = $link->first();
            $link_tags = ( is_array($link->tags) ? $link->tags : explode(',', $link->tags) ) ?? [] ;
            $selected_tags = Tag::whereIn('id',$link_tags)->get();
            $unselected_tags = Tag::whereNotIn('id',$link_tags)->get();
            $check_unique = true;
            $this->data = [
                'selected_tags' => $selected_tags->toJson(),
                'unselected_tags' => $unselected_tags->toJson(),
                'check_unique' => $check_unique
            ]; 
            return $this->apiOutput(Response::HTTP_OK, "Link exists ...");
        }else{
            $this->apiSuccess();
            $this->data = [
                'check_unique' => $check_unique
            ]; 
            return $this->apiOutput(Response::HTTP_OK, "Unique Link ...");
        }

    }

    public function bulkInput(Request $request)
    {
         
        
 
    }

    public function insert(Request $request){
        $new_request = $request->except(['_token']);
        $request_result = false;
        foreach ($new_request as $value)
            $request_result = $request_result || ($value != null);
        $request_result = ($request->file && $request->file != 'undefined') || $request_result;

        
        if($request_result ){
            
            if($request->tags){
                if( ! is_array($request->tags) )
                    $request->tags = explode(",",$request->tags);
                

                $tag_values = [];
                foreach($request->tags as $tag){
                    if( ! is_numeric($tag) and ! Tag::where('name',$tag)->first() ){
                        
                        $tagObj = new Tag();
                        $tagObj->name = $tag;
                        $tagObj->causer_id = Auth::user()->id;
                        $tagObj->save();
                        $tag = $tagObj->id;

                        $text = "New Tag '$tag' added";
                    }
                    $tag_values[] = $tag;
                }
                
                $request->tags = $tag_values;

                if($request->link){
                    $link = Link::where('link',$request->link);
                    if($link->count() > 0){
                        $link->update(['tags' => $request->tags]);
                        $message = "Link updated ...";
                    }else{
                        Link::create($request->only('link','tags'));
                        $message = "New Link created ...";
                    }    
                }
            }
            
            if($request->file && $request->file != 'undefined'){
                $validatedData = $request->validate([
                    'file' => 'required|max:2048',
                ]);
                $name = $request->file('file')->getClientOriginalName();
                $path = $request->file('file')->store('public/files');
                
                
                $fileName = auth()->id() . '_' . time() . '.'. $request->file->extension();  
                // dd(public_path(''), $fileName);
                $request->file->move(public_path(''), $fileName);
                

                

                $number = 0;
                $records = [];
                $lines = file($fileName);
                
                foreach($lines as $line){
                    $link = trim(preg_replace('/\s\s+/', ' ', $line));
                    $records[] = $link;
                    // $result = Link::firstOrCreate(['link'=> $link, 'bulkin'=>true ]);
                    // if($result->wasRecentlyCreated)
                    //     $number++;
                }
                
                $rows = $records;
                $matched_result = Link::whereIn('link',$rows)->pluck('link')->toArray();
                foreach($matched_result as $result)
                    $temp[$result] = 1;
                
                $data = [];
                foreach($rows as $row){
                    
                    if(!isset($temp[$row]) and $row != ''){
                        $number++;
                        $data[] = [ 'link' => $row , 'tags' => json_encode($request->tags) ];
                    }
                }
                    
                Link::insert($data);

                $this->apiSuccess();
                return $this->apiOutput(Response::HTTP_OK, $number." Links added ...");
                
            }
            

            // $myfile = fopen("contents.list", "a") or die("Unable to open file!");
            // $txt = $request->link;
            // fwrite($myfile, "\n". $txt);
            // fclose($myfile);

            $this->apiSuccess();
            return $this->apiOutput(Response::HTTP_OK, $message  ?? " Links added ...");  
            // return $this->listLinks('New People added ...');
        }else{
            return $this->apiOutput(Response::HTTP_OK, "Minimum one field is required ...");
        }
        
    }

    public function listTags(){
        $tags = Tag::get();
        $this->data = $tags->toJson();
        $this->apiSuccess();
        return $this->apiOutput(Response::HTTP_OK, "All properties listed ...");
    }

    public function lineCount($file){
        $linecount = 0;
        $handle = fopen($file, "r");
        while(!feof($handle)){
        $line = fgets($handle);
        $linecount++;
        }
        fclose($handle);
        return $linecount;
    }

    public function randomPage(){
        return view('link.random');
    }

    public function randomChoose(Request $request,$file="input.list"){
        $tags = explode(',',$request->tags);
        // SELECT * from `links` WHERE JSON_CONTAINS(tags, '"2"','$')
        $link = Link::where(function($query) use($tags){
     
            $query->whereJsonContains('tags', $tags[0]);
    
            for($i = 1; $i < count($tags); $i++) {
               $query->WhereJsonContains('tags', $tags[$i]);      
            }
    
            return $query;
        })->get();
        if($link->count()){
            $link = $link->random();
            $link = $link->link;
            $this->apiSuccess();
            $this->data = $link;
            return $this->apiOutput(Response::HTTP_OK, "Random Link picked ...");
        }else{
            return $this->apiOutput(Response::HTTP_NOT_FOUND, "No Link picked ...");
        }
      
        // $link = Link::where('bulkin',true)->get()->random();
        

        
    }

    public function addInfo(Request $request){
        // people_id
        // dd($request->all());
        $text = null;
        $property_id = null;
        if( ! is_numeric($request->property) and ! Property::where('name',$request->property)->first() ){
            $property = new Property();
            $property->name = $request->property;
            $property->causer_id = Auth::user()->id;
            $property->save();
            $property_id = $property->id;
            $text = "New Property '$request->property' added";
        }

         
        $value = new Value();
        $value->people_id = $request->people_id;
        $value->property_id = $property_id ?? $request->property;
        $value->value = $request->value;
        $value->save();

        $text = $text ? $text." and data added ..." : "Data added ...";
        
        $this->apiSuccess();
        return $this->apiOutput(Response::HTTP_OK, $text);
        
    }
}
