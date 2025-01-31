<?php

namespace App\Http\Controllers;
use App\Models\News;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\NewsRequest;
use Illuminate\Support\Facades\Auth;
 
use function PHPUnit\Framework\isEmpty;

class NewsController extends Controller
{
    public function search(Request $request)
    {
        try{
            $query = $request->input('query');
            $listNew = News::where('admin.name', 'like', '%' . $query . '%')
                            ->orWhere('title', 'like', '%' . $query . '%')
                            ->orWhere('content', 'like', '%' . $query . '%')
                            ->paginate(5);
            return view('news.list', compact('listNew', 'query'));
        }catch(Exception $e){
            return back()->with(['Error'=>'Không tìm thấy khách hàng']);
        }
    }
    public function getList(){
        $listNews = News::paginate(5);
        return view('news.list',compact('listNews'));
    }

    public function addNew(){
        return view('news.add-new');
    }
    public function hdAddNew(NewsRequest $request){
        try{
            $file = $request->file('img_url');
            $news= new News();

            if (isset($file)) {
                $path = $file->store('new');

                $news->img_url = $path;
            }
            $news->admin_id=Auth::user()->id;
            $news->title= $request->title;
            $news->content= $request->content;
            $news->save();
            return redirect()->route('news.list')->with(['Success'=>"Thêm mới tin tức thành công !"]);

        }catch(Exception $e){
            return back()->withInput()->with("error: ".$e);
        }
    }
    public function upDate($id){
        $news = News::findOrFail($id);
        if(empty($news)){
            return redirect()->route('news.list')->with(['Error'=>"Tin này không tồn tại!"]);
        }
        return view('news.update', compact('news'));
    }
    public function hdUpdate(Request $request, $id){
        try{
            $file = $request->file('img_url');
            $news = News::findOrFail($id);

            if (isset($file)) {
                $path = $file->store('new');

                $news->img_url = $path;
            }
        $news->admin_id=Auth::user()->id;
        $news->title= $request->title;
        $news->content= $request->content;
        $news->save();
        return redirect()->route('news.list')->with(['Success'=>"Cập nhật tin tức thành công!"]);
        }catch(Exception $e){
            return back()->withInput()->with(['error' => "Error: " . $e->getMessage()]);
        }
    }
    public function delete($id){
        $news=News::findOrFail($id);
        $news->delete();
                return redirect()->route('news.list')->with(['Success'=>" Xóa tin thành công!"]);
    }
}
