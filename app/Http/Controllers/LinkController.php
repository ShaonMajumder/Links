<?php

namespace App\Http\Controllers;

use App\Http\Components\Message;
use App\Models\Link;
use App\Models\Tag;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LinkController extends Controller
{
    use Message;
    public function listLinks($message=null){
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

    public function checkUniqueLink(Request $request){
        $link = Link::where('link',$request->link);
        if($link->count() > 0){
            $link = $link->first();
            $tags = Tag::whereIn('id',$link->tags)->get();
            $this->data = $tags->toJson();
            return $this->apiOutput(Response::HTTP_OK, "Link exists ...");
        }else{
            $this->apiSuccess();
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
                $i = 0;
                foreach($request->tags as $tag){
                    if( ! is_numeric($tag) and ! Tag::where('name',$tag)->first() ){
                        $tagObj = new Tag();
                        $tagObj->name = $tag;
                        $tagObj->causer_id = Auth::user()->id;
                        $tagObj->save();
                        $tag = $tagObj->id;
                        $text = "New Tag '$tag' added";
                    }
                    $tag_values[$i] = $tag;
                    $i++;
                }
                $request->merge(['tags' => $tag_values]);

                $link = Link::where('link',$request->link);
                if($link->count() > 0){
                    $link->update(['tags' => $request->tags]);
                }else{
                    Link::create($request->only('link','tags'));
                }
            }
            
            if($request->file && $request->file != 'undefined'){
                // dd($request->file);
                $validatedData = $request->validate([
                    'file' => 'required|max:2048',
                ]);
                $name = $request->file('file')->getClientOriginalName();
                $path = $request->file('file')->store('public/files');
                
                
                $fileName = auth()->id() . '_' . time() . '.'. $request->file->extension();  
                // dd(public_path(''), $fileName);
                $request->file->move(public_path(''), $fileName);
                $lines = file($fileName);
                
                $records = array();
                foreach($lines as $line){
                    $link = trim(preg_replace('/\s\s+/', ' ', $line));
                    
                    array_push($records,array('link'=>$link));
                }
                // dd(        $records);
                Link::insert($records);
                
            }
            

            // $myfile = fopen("contents.list", "a") or die("Unable to open file!");
            // $txt = $request->link;
            // fwrite($myfile, "\n". $txt);
            // fclose($myfile);

            $this->apiSuccess();
            return $this->apiOutput(Response::HTTP_OK, "New Link added ...");  
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
    public function random($file="input.list"){
        $linecount = $this->lineCount($file);
        $random_line_number = rand(1, $linecount);
        $lines = file($file);
        $link = $lines[$random_line_number];
        $link = trim(preg_replace('/\s\s+/', ' ', $link));

        

 ?>

    <script type="text/javascript">
        
        
    (function() {
   // your page initialization code here
   // the DOM will be available here
   window.open("<?php echo $link; ?>", "_blank");
})();
        
    </script>

<?php

        
        // $tags = Tag::get();
        // $this->data = $tags->toJson();
        // $this->apiSuccess();
        // return $this->apiOutput(Response::HTTP_OK, "All properties listed ...");
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
