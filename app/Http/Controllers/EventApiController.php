<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventApiController extends Controller
{
    public function index() {
        return Event::all();
    }


    public function store() {
        return $this->process_insert();
    }



    public function get_instance(){

        $start_date = request("from");
        $end_date = request("to");

        $data = Event::where('startDateTime','>=',$start_date)
                ->where('startDateTime','<=',$end_date)
                ->get();
                
        return $data;        
    }


    public function update(Event $event){

       $data = array();

        $frequency = request("frequency");
        $start_date = request("startDateTime");
        $end_date = request("endDateTime");
        $duration = request("duration");
        $eventName = request("eventName");
        $invitees = request('invitees');
        
        if($frequency){
            $data['frequency'] = $frequency;
        }

        if($start_date){
            $data['startDateTime'] = $start_date;
        }

        if($end_date){
            $data['endDateTime'] = $end_date;
        }

        if($duration){
            $data['duration'] = $duration;
        }

        if($eventName){
            $data['eventName'] = $eventName;
        }

        if($invitees){
            $data['invitees'] = $invitees;
        }
    
        $success =  $event->update($data);
        return [
            'success' => $success
        ];

    }

    public function destroy(Event $event){

        $success =  $event->delete();
        return [
            'success' => $success
        ];

    }    

    private function process_insert(){
        $frequency = request("frequency");
        $start_date = request("startDateTime");
        $end_date = request("endDateTime");
        $duration = request("duration");

        $result = array();
        $valid = true;
        $insert_data= array(); 
        $now = date("Y-m-d H:i");

        try {

            $valid_ids = array();
            $invetees_id = json_decode(request("invitees"),true);
            
            if(empty($invetees_id)){
                throw new \Exception("Invalid invitees id");
            } 

            foreach($invetees_id as $id){
                    if(!is_int( $id )){
                        throw new \Exception("Invitees id must be Integer");
                    } 

                    if( in_array($id,$valid_ids) ){
                        throw new \Exception("Dupilicate Invitees id");
                    } else {
                        $valid_ids[] = $id;
                    }
            }
            


            if($frequency=="Once-Off"){
                /* 
                a one time event occuring from startDateTime up to the defined duration.
                endDateTime should be null
                */
                    
                if(!empty($end_date)){
                   throw new \Exception("End date must be null");
                        
                }
    
                $from = $start_date;
                $to = date("Y-m-d H:i:s",strtotime("$start_date +$duration minutes" ) );
                $end_date = $to;
                
               
                $is_conflict = $this->check_schedule_conflict($from,$to);
                if($is_conflict){
                    throw new \Exception("Conflict in schedule for $from - $to");
                }


                $insert_item= array( 
                    'eventName' => request('eventName'), 
                    'frequency' => request('frequency'),
                    'duration' => request('duration'),
                    'startDateTime' => request('startDateTime'),
                    'endDateTime' =>  $end_date,
                    'invitees' => request('invitees'),
                    "created_at" => $now
                );

                $insert_data[] = $insert_item;
    
    
            }elseif($frequency=="Weekly"){
                /*
                a recurring event happening within startDateTime up to endDateTime.
                a recurring event beginning from startDateTime if endDateTime is not provided.
                use startDateTime's day of week as reference for the recurring schedule.
                duration value should not cause two event instance to overlap. 
                */        


                $stamp_start = strtotime($start_date);
                $stamp_end = strtotime($end_date);
                $current_stamp = $stamp_start;

                ##add recurring value here
                while( $stamp_end >  $current_stamp){
                    $current_val = date("Y-m-d H:i",$current_stamp);
                    $end_date =  date("Y-m-d H:i",strtotime("$current_val +$duration minutes"));   

                    $from  = $current_val;
                    $to = $end_date;
                    $is_conflict = $this->check_schedule_conflict($from,$to);
                    if($is_conflict){
                        throw new \Exception("Conflict in schedule for $from - $to");
                    }    

                    $insert_item= array( 
                        'eventName' => request('eventName'), 
                        'frequency' => request('frequency'),
                        'duration' => request('duration'),
                        'startDateTime' => $current_val,
                        'endDateTime' =>  $end_date,
                        'invitees' => request('invitees'),
                        "created_at" => $now
                    ); 
                    $insert_data[] = $insert_item;    
 
                    $current_stamp = strtotime("$current_val +7 days");
                }

            }elseif($frequency=="Monthly"){
                /*
                    a recurring event happening within startDateTime up to endDateTime.
                    a recurring event beginning from startDateTime if endDateTime is not provided.
                    use startDateTime's day of month as reference for the recurring schedule.
                    if day of month reference is not valid for a month (ie. 31). use the last day of that month.
                    duration should not cause two event instance to overlap.
                    invitees value is an array of valid and unique userIds.
                */

                $stamp_start = strtotime($start_date);
                $stamp_end = strtotime($end_date);
                $current_stamp = $stamp_start;

                $start_date_date_only = date("Y-m-d",$current_stamp);
                $day_value = explode("-",$start_date_date_only);
                $original_day = $day_value[2];

                while( $stamp_end >  $current_stamp){
                    
                    $date_value_of_current_moth = date("Y-m-t H:i",$current_stamp);
                    $date_value_of_current_moth_stamp = date("Y-m-t",$current_stamp);

                    $explode_date_value_of_current_moth = explode("-",$date_value_of_current_moth);
                    $date_value_of_current_moth = $explode_date_value_of_current_moth[2];

                    $start_date_date_only = date("Y-m-d",$current_stamp);
                    $day_value = explode("-",$start_date_date_only);
                    $day_value = $day_value[2];

                    if($day_value > $date_value_of_current_moth){
                        $current_val = $date_value_of_current_moth_stamp;
                        $end_date =  date("Y-m-d H:i",strtotime("$current_val +$duration minutes"));   
                    } else {                        
                        $current_val = date("Y-m-d H:i",$current_stamp);
                        $end_date =  date("Y-m-d H:i",strtotime("$current_val +$duration minutes"));       
                    }

                    $from  = $current_val;
                    $to = $end_date;
                    $is_conflict = $this->check_schedule_conflict($from,$to);
                    if($is_conflict){
                        throw new \Exception("Conflict in schedule for $from - $to");
                    }    

                    $insert_item= array( 
                        'eventName' => request('eventName'), 
                        'frequency' => request('frequency'),
                        'duration' => request('duration'),
                        'startDateTime' => $current_val,
                        'endDateTime' =>  $end_date,
                        'invitees' => request('invitees'),
                        "created_at" => $now
                    ); 
                    $insert_data[] = $insert_item;    
                    
                    
                    $month_prev = date("m",strtotime($current_val));
                    $current_stamp = strtotime("$current_val +1 Month");
                    $month_next =  date("m",$current_stamp);

                    $interval_mos = $month_next - $month_prev;

                    #for invalid date
                    if($interval_mos >=2 ){
                        $current_stamp = strtotime("$current_val +1 Month");
                        $d = date("Y-m-d H:i",$current_stamp);
                        $current_val = date("Y-m-t H:i", strtotime( "$d  -1 Month"));
                        $current_stamp = strtotime($current_val);
                    } else {

                        $minute_val = date("H:i",$current_stamp);
                        $year_month_val = date("Y-m",$current_stamp);
                        $current_val = "$year_month_val-$original_day $minute_val";
                        $current_stamp = strtotime($current_val);
                    }
                }
             
            } else {
                throw new \Exception("Frequency Not Valid");
            }

            $insert = Event::insert($insert_data);





            if($insert){
                return array(
                    "status" => "Success",
                    "message" => "Record Created"
                );
            } else {
                throw new \Exception("Could Not Create");
            }


        } catch (\Exception $e) { 
            return array(
                "status" => "Failed",
                "message" => $e->getMessage()
            );
        }

    }


    private function check_schedule_conflict($from,$to){
        $data = Event::whereBetween('startDateTime',[$from,$to])
                ->orWhereBetween('endDateTime',[$from,$to])
                ->limit(1)
                ->get()
                ->all();
        foreach($data as $item){
            if( !empty($item->eventName)) {
                return true;
            }
                
        }

        return false;

    }
}
