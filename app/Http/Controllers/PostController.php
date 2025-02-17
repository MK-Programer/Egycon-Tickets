<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\TicketType;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Exception;
use Postmark\PostmarkClient;
use Session;
use Postmark\Models\PostmarkException;
use DB;
class PostController extends Controller
{
    
    public function view()
    {
        $ticket_types = TicketType::all();
        return view('form',["ticket_types"=>$ticket_types]);
    }

    public function edit_requests()
    {
        return view('admin.edit-requests');
    }

    public function action()
    {
        
        $data = DB::table('posts')->where('code',$_POST['code'])->get();
        if($data->isEmpty()){
            return back()->with('message', 'Code # '.$_POST['code'].' Not Found');
        }else{
            // return back()->with('message', 'Code # '.$_POST['code'].' Found');
            $status = DB::table('posts')->where('code', $_POST['code'])->value('status');
            if($status == 0 || $status == null){
                return back()->with('message', 'Status = 0 or Status = null');
            }else if($status == 1){
                $result = DB::update('update posts set status=? where code = ?',[2,$_POST['code']]);
                if($result){
                    return back()->with('message', 'Accepted and Changed to 2');
                }else{
                    return back()->with('message', 'Error while updating');
                }
            }else if($status == 2){
                return back()->with('message', 'Scanned Before');
            }
        }

    }

    public function store(Request $request)
    {
        
        $request->validate([
            'name'=>"required|string|min:6|max:64",
            'email' => "required|email|unique:posts,email",
            'phone_number' => "required",
            'receipt'=>"required|file|mimes:png,jpg,jpeg",
            'ticket_type_id'=>"required|integer|exists:ticket_types,id",
        ]);
        $post = new Post;
        // check that the selected file is image and save it to a folder
        // $post->receipt = $request->receipt;
        if($request->hasFile('receipt')){
            $image = $request->file('receipt');
            $image_name = $image->getClientOriginalName();
            $image->move(public_path('/images'), $image_name);
            $post->picture = $image_name;
        }
        $post->name = $request->name;
        $post->ticket_type_id = $request->ticket_type_id;
        // check that the name is chars only
        if(preg_match("^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z])$^", $post->name) == 0){
           return redirect()->back()->with('status-failure', 'Name should be characters only!');
        }
        // check that it is a correct type of emails
        $post->email = $request->email;
        if (!filter_var($post->email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('status-failure', 'Not a valid email address!');
        }
        // check that it is numbers only
        $post->phone_number = $request->phone_number;
        if(preg_match('@[0-9]@', $post->phone_number) == 0 ){
            return redirect()->back()->with('status-failure', 'Phone number must be numbers only!');
        }
        $unique_id = uniqid();
        $qr_options = new QROptions([
            'version'    => 5,
            'outputType' => QRCode::OUTPUT_IMAGE_JPG,
            'eccLevel'   => QRCode::ECC_L,
            'imageTransparent'=>false,
            'imagickFormat'=>'jpg',
            'imageTransparencyBG'=>[255,255,255],
        ]);        
        $qrcode = new QRCode($qr_options);
        $qrcode->render($unique_id, public_path('images/qrcodes/'.$unique_id.".jpg"));
        $post->code = $unique_id;
        $post->save();
        return redirect()->back()->with('status-success', 'Thank you for registering at Egycon 9. An email will be sent to you once your request is reviewed.');
    }
    private function send_email($request){
        try {
            $client = new PostmarkClient(env("POSTMARK_TOKEN"));
            $sendResult = $client->sendEmailWithTemplate(
                "info@gamerslegacy.net",
                $request->email,
                26959536,
                [
                    "name"=>explode(' ',$request->name)[0],
                    "ticket_type"=>$request->ticket_type->name." Ticket - ". $request->ticket_type->price,
                    "date"=>date('Y/m/d'),
                    "action_url"=>"#",
                    "qrcode"=>"#"
                ]
            );

            // Getting the MessageID from the response
            echo $sendResult->MessageID;
        } catch (PostmarkException $ex) {
            // If the client is able to communicate with the API in a timely fashion,
            // but the message data is invalid, or there's a server error,
            // a PostmarkException can be thrown.
            echo $ex->httpStatusCode;
            echo $ex->message;
            echo $ex->postmarkApiErrorCode;
        } catch (Exception $generalException) {
            // A general exception is thrown if the API
            // was unreachable or times out.
        }
    }
    public function view_requests(){
        $posts = Post::with('ticket_type')->orderBy('status')->paginate(15);
        return view('admin.requests',['requests'=>$posts]);
    }

    public function accept($id){
        $post = Post::with('ticket_type')->findOrFail($id);
        $this->send_email($post);
        $post->status = 1;
        $post->save();
        return redirect()->back()->with(["success"=>"{$post->name}'s request has been accepted successfully!"]);
    }
    public function reject($id)
    {
        $post = Post::with('ticket_type')->findOrFail($id);
        $post->status = 0;
        $post->save();
        return redirect()->back()->with(["success" => "{$post->name}'s request has been rejected successfully!"]);
    }
}