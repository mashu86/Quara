<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderShipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class BulkArticleNumberController extends Controller
{
    private function bucket(Request $request): array { return $request->session()->get('article_bucket', []); }
    private function address(Order $o): string { return collect([$o->house_building,$o->street,$o->area,$o->city,$o->district,$o->state,$o->pin_code])->filter()->implode(', '); }

    public function index(Request $request)
    {
        $today = now('Asia/Kolkata')->toDateString();
        $start = $request->input('start_date', $today); $end = $request->input('end_date', $today);
        $q = Order::with('shipment')->where('payment_status','paid')->whereIn('order_status',['confirmed','processing','packed','shipped','delivered']);
        $q->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '>=', $start)->whereDate(DB::raw('COALESCE(sale_date, created_at)'), '<=', $end);
        if ($request->filled('client_name')) $q->where('customer_name','like','%'.$request->client_name.'%');
        if ($request->input('tracking_status','not_yet') === 'not_yet') $q->whereDoesntHave('shipment')->where(function($x){$x->whereNull('tracking_number')->orWhere('tracking_number','');});
        elseif ($request->input('tracking_status') === 'generated') $q->where(fn($x)=>$x->whereHas('shipment')->orWhereNotNull('tracking_number'));
        $orders = $q->orderByDesc(DB::raw('COALESCE(sale_date, created_at)'))->paginate(50)->withQueryString();
        $bucket = Order::whereIn('id',$this->bucket($request))->with('shipment')->get()->keyBy('id');
        return view('admin.bulk_article.index', compact('orders','bucket','start','end'));
    }

    public function proceed(Request $request)
    {
        $ids = array_map('intval', $request->input('order_ids', []));
        $valid = Order::whereIn('id',$ids)->where('payment_status','paid')->whereIn('order_status',['confirmed','processing','packed','shipped','delivered'])->whereDoesntHave('shipment')->whereNull('tracking_number')->pluck('id')->all();
        $bucket = array_values(array_unique(array_merge($this->bucket($request),$valid)));
        $request->session()->put('article_bucket',$bucket);
        return back()->with('success', count($valid).' eligible orders added to processing bucket.');
    }
    public function remove(Request $request, int $order) { $request->session()->put('article_bucket',array_values(array_diff($this->bucket($request),[$order]))); return back(); }
    public function uploadPage(Request $request)
    {
        $orders=Order::whereIn('id',$this->bucket($request))->get()->keyBy('id');
        abort_if($orders->isEmpty(), 400, 'Add orders to the processing bucket first.');
        return view('admin.bulk_article.upload',compact('orders'));
    }

    public function parse(Request $request)
    {
        $request->validate(['courier_file'=>'required|file|mimes:csv,txt,xlsx,xls|max:10240']);
        $file=$request->file('courier_file'); $path=$file->getRealPath();
        $spreadsheet=IOFactory::load($path);
        $rows=$spreadsheet->getActiveSheet()->toArray(null,true,true,false);
        $spreadsheet->disconnectWorksheets();
        abort_if(count($rows)<2,422,'The courier file has no data rows.');
        $headers=array_map(fn($v)=>trim((string)$v),array_shift($rows));
        $request->session()->put('article_import',['headers'=>$headers,'rows'=>$rows]);
        return redirect()->route('admin.bulk-article.upload')->with('success','File loaded. Map its columns to continue.');
    }

    public function match(Request $request)
    {
        $request->validate(['map'=>['required','array'],'map.receiver_name'=>'required|integer|min:0','map.receiver_address'=>'required|integer|min:0','map.article_number'=>'required|integer|min:0','map.weight'=>'required|integer|min:0','map.courier_charge'=>'required|integer|min:0','map.pin_code'=>'nullable|integer|min:0','map.tracking_number'=>'nullable|integer|min:0','map.courier_name'=>'nullable|integer|min:0']);
        $import=$request->session()->get('article_import'); abort_unless($import,422,'Upload a courier file first.');
        $map=$request->input('map'); $orders=Order::whereIn('id',$this->bucket($request))->with('shipment')->get(); $used=[];$results=[];
        foreach($map as $column) if($column!=='' && (int)$column>=count($import['headers'])) abort(422,'A mapped column is outside the uploaded file.');
        foreach($import['rows'] as $index=>$row){$get=fn($key)=>isset($map[$key])&&$map[$key]!==''?trim((string)($row[$map[$key]]??'')):'';$name=$get('receiver_name');$addr=$get('receiver_address');$pin=$get('pin_code');$article=$get('article_number');$track=isset($map['tracking_number'])&&$map['tracking_number']!==null&&$map['tracking_number']!==''?$get('tracking_number'):$article;$weight=$get('weight');$charge=$get('courier_charge');$courier=$get('courier_name');
            $status='Not Matched';$order=null;
            if(!$name||!$addr||!$article||!is_numeric($weight)||(float)$weight<0||!is_numeric($charge)||(float)$charge<0)$status='Invalid';
            elseif(in_array(Str::lower($article),$used,true)||($track&&in_array(Str::lower($track),$used,true)))$status='Duplicate';
            elseif(OrderShipment::where('article_number',$article)->orWhere('tracking_number',$article)->when($track,fn($q)=>$q->orWhere('article_number',$track)->orWhere('tracking_number',$track))->exists())$status='Duplicate';
            else {
                $nn=$this->normalize($name);
                $candidates=$orders->filter(fn($o)=>$this->normalize($o->customer_name)===$nn)->map(function($o)use($addr,$pin){
                    $orderPin=$this->normalize((string)$o->pin_code);$givenPin=$this->normalize($pin);
                    $a=preg_split('/[^a-z0-9]+/i',Str::lower($addr),-1,PREG_SPLIT_NO_EMPTY);$b=preg_split('/[^a-z0-9]+/i',Str::lower($this->address($o)),-1,PREG_SPLIT_NO_EMPTY);
                    $a=array_unique(array_filter($a,fn($v)=>strlen($v)>2));$b=array_unique(array_filter($b,fn($v)=>strlen($v)>2));$overlap=count(array_intersect($a,$b));$score=$givenPin&&$orderPin===$givenPin?2:($overlap/max(1,min(count($a),count($b))));
                    return ['order'=>$o,'score'=>$score];
                })->filter(fn($c)=>$c['score']>=0.45)->sortByDesc('score')->values();
                if($candidates->count()===1||($candidates->count()>1&&$candidates[0]['score']>$candidates[1]['score'])){$order=$candidates[0]['order'];$status=$order->shipment||$order->tracking_number?'Already Generated':'Matched';}
            }
            if($article)$used[]=Str::lower($article);if($track)$used[]=Str::lower($track);
            $results[]=['row'=>$index,'order_id'=>$order?->id,'order_number'=>$order?->order_number,'name'=>$name,'address'=>$addr,'status'=>$status,'article'=>$article,'tracking'=>$track,'weight'=>$weight,'charge'=>$charge,'courier'=>$courier];
        }
        $request->session()->put('article_results',$results);$request->session()->put('article_map',$map);
        return view('admin.bulk_article.results',['orders'=>$orders,'results'=>$results,'counts'=>collect($results)->countBy('status')]);
    }

    private function normalize(string $value): string { return preg_replace('/[^a-z0-9]+/','',Str::lower(trim($value))); }

    public function save(Request $request)
    {
        $results=$request->session()->get('article_results',[]);abort_unless($results,422,'No import results to save.');$saved=[];
        DB::transaction(function()use($results,$request,&$saved){foreach($results as $r){if($r['status']!=='Matched'||!$r['order_id'])continue;
            $order=Order::whereKey($r['order_id'])->lockForUpdate()->first();if(!$order||$order->payment_status!=='paid'||!in_array($order->order_status,['confirmed','processing','packed','shipped','delivered'])||$order->shipment||$order->tracking_number)continue;
            $shipment=OrderShipment::create(['order_id'=>$order->id,'article_number'=>$r['article'],'tracking_number'=>$r['tracking'],'courier_name'=>$r['courier']?:null,'receiver_name'=>$r['name'],'receiver_address'=>$r['address'],'weight'=>$r['weight'],'weight_unit'=>'gram','courier_charge'=>$r['charge'],'shipment_date'=>now('Asia/Kolkata')->toDateString(),'shipment_status'=>'Generated','created_by'=>$request->user()->id]);
            DB::table('order_shipment_histories')->insert(['order_shipment_id'=>$shipment->id,'user_id'=>$request->user()->id,'old_values'=>null,'new_values'=>json_encode($shipment->getAttributes()),'created_at'=>now(),'updated_at'=>now()]);$saved[]=$order->id;
        }});
        $request->session()->put('article_bucket',array_values(array_diff($this->bucket($request),$saved)));$request->session()->forget(['article_import','article_results','article_map']);
        return redirect()->route('admin.bulk-article.index')->with('success',count($saved).' shipment records saved.');
    }

    public function history(Request $request)
    {
        $q=OrderShipment::with(['order.items.product.images','order.items.product.category','order.operations'])->orderByDesc('created_at');foreach(['article_number','tracking_number','courier_name'] as $field)if($request->filled($field))$q->where($field,'like','%'.$request->$field.'%');if($request->filled('client_name'))$q->where('receiver_name','like','%'.$request->client_name.'%');if($request->filled('order_id'))$q->whereHas('order',fn($o)=>$o->where('order_number','like','%'.$request->order_id.'%'));if($request->filled('status'))$q->where('shipment_status',$request->status);if($request->filled('start_date'))$q->whereDate('shipment_date','>=',$request->start_date);if($request->filled('end_date'))$q->whereDate('shipment_date','<=',$request->end_date);
        // With no filter values, show the complete saved shipment history by default.
        $shipments=$q->get();return view('admin.bulk_article.history',compact('shipments'));
    }
    public function edit(OrderShipment $shipment) { $shipment->load('order'); return view('admin.bulk_article.edit',compact('shipment')); }
    public function update(Request $request,OrderShipment $shipment)
    {
        $data=$request->validate(['article_number'=>'nullable|string|max:255|unique:order_shipments,article_number,'.$shipment->id,'tracking_number'=>'nullable|string|max:255|unique:order_shipments,tracking_number,'.$shipment->id,'courier_name'=>'nullable|string|max:255','weight'=>'nullable|numeric|min:0','weight_unit'=>'required|string|max:20','courier_charge'=>'nullable|numeric|min:0','shipment_date'=>'nullable|date','shipment_status'=>'required|string|max:100','receiver_name'=>'required|string|max:255','receiver_address'=>'required|string']);
        DB::transaction(function()use($shipment,$data,$request){$old=$shipment->getAttributes();$shipment->fill($data);$shipment->updated_by=$request->user()->id;$shipment->save();DB::table('order_shipment_histories')->insert(['order_shipment_id'=>$shipment->id,'user_id'=>$request->user()->id,'old_values'=>json_encode($old),'new_values'=>json_encode($shipment->getAttributes()),'created_at'=>now(),'updated_at'=>now()]);});return redirect()->route('admin.bulk-article.history')->with('success','Shipment updated.');
    }
    public function destroy(OrderShipment $shipment)
    {
        $this->deleteShipmentRecords([$shipment->id]);

        return redirect()->route('admin.bulk-article.history')->with('success', 'Tracking record deleted.');
    }
    public function bulkDestroy(Request $request)
    {
        $data = $request->validate(['shipment_ids' => 'required|array|min:1', 'shipment_ids.*' => 'required|integer|distinct|exists:order_shipments,id']);
        $deleted = $this->deleteShipmentRecords($data['shipment_ids']);

        return redirect()->route('admin.bulk-article.history')->with('success', $deleted . ' tracking record(s) deleted.');
    }
    private function deleteShipmentRecords(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $shipments = OrderShipment::whereIn('id', $ids)->lockForUpdate()->get();
            foreach ($shipments as $shipment) {
                $order = Order::whereKey($shipment->order_id)->lockForUpdate()->first();
                if ($order && in_array($order->tracking_number, array_filter([$shipment->article_number, $shipment->tracking_number]), true)) {
                    $order->tracking_number = null;
                    $order->save();
                }
                // Shipment history rows are removed by the foreign key cascade.
                $shipment->delete();
            }
            return $shipments->count();
        });
    }
    public function manual(Order $order) { return view('admin.bulk_article.manual',compact('order')); }
    public function storeManual(Request $request,Order $order)
    {
        abort_unless($order->payment_status==='paid'&&in_array($order->order_status,['confirmed','processing','packed','shipped','delivered']),403);
        $data=$request->validate(['article_number'=>'nullable|required_without:tracking_number|string|max:255|unique:order_shipments,article_number','tracking_number'=>'nullable|required_without:article_number|string|max:255|unique:order_shipments,tracking_number','courier_name'=>'nullable|string|max:255','weight'=>'nullable|numeric|min:0','weight_unit'=>'required|string|max:20','courier_charge'=>'nullable|numeric|min:0','shipment_date'=>'nullable|date','receiver_name'=>'required|string|max:255','receiver_address'=>'required|string']);
        abort_if($order->shipment||$order->tracking_number,409,'Tracking information already exists.');$s=OrderShipment::create($data+['order_id'=>$order->id,'shipment_status'=>'Generated','created_by'=>$request->user()->id]);DB::table('order_shipment_histories')->insert(['order_shipment_id'=>$s->id,'user_id'=>$request->user()->id,'old_values'=>null,'new_values'=>json_encode($s->getAttributes()),'created_at'=>now(),'updated_at'=>now()]);return redirect()->route('admin.orders.show',$order)->with('success','Shipment created.');
    }
}
