<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Todo;

use DateTime;
class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $toDos = Todo::all();
        $toDos = $this->_calcTimeLeft($toDos->toArray());

        return response()->json($toDos,200);
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
    public function store(Request $request)
    {
        if (!$request->title || !$request->duration) {
            return response()->json('Both title and duration fields are required',200);
        }else{
            $tasks = $request->all();
            $toDo = Todo::create($tasks);
            return response()->json($toDo,200);
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $toDo = Todo::orderBy('id', 'DESC')->where('id',$id)->first();
        $toDos[0] = $toDo->toArray();
        $toDos = $this->_calcTimeLeft($toDos);
        return response()->json($toDos,200);
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
    public function update(Request $request, $id)
    {
        $toDo = Todo::findOrFail($id);
        $checkTimeLeft = $this->_checkTimeLeft($toDo->toArray());
        if(false == $toDo->paused && $checkTimeLeft > 0)
        {
            $toDo->duration = $checkTimeLeft;
            $toDo->timer_stopped_at = $checkTimeLeft;
        }else{
            $toDo->duration = 0;
            $toDo->timer_stopped_at = 0;
        }
        $toDo->update($request->all());
        return response()->json($toDo,200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $toDo = Todo::findOrFail($id);
        $toDo->delete();
        return response()->json('Task id :'.$id.' successfully deleted',200);
    }
    /**
     * Calculate the duration left for the task
     *
     * @param $toDos array
     * @return $toDos array with updated duration
     */
    private function _calcTimeLeft($toDos){
        if(!$toDos[0]){$toDos[0] = $toDos;}
        foreach($toDos as $key => $toDo){
            if($toDo['duration'] > 0 && $toDo['timer_stopped_at'] > 0){
                if(true == $toDo['paused']){
                    $toDos[$key]['duration'] = $toDo['timer_stopped_at'];
                }else{
                    $toDos[$key]['duration'] = $this->_checkTimeLeft($toDo);
                    $toDos[$key]['timer_stopped_at'] = $toDos[$key]['duration'];
                } 
            }elseif($toDo['duration'] > 0){
                $toDos[$key]['duration'] = $this->_checkTimeLeft($toDo);
            } 
        }
        return $toDos;
    }

     /**
     * Calculate the duration left for the task
     *
     * @param $toDo dataset
     * @return the duration left
     */

    private function _checkTimeLeft($toDo){
        $currentTime = new DateTime();
        if(0 == $toDo['timer_stopped_at']){
            $modifiedTime = new DateTime($toDo['created_at']);
        }else{
            $modifiedTime = new DateTime($toDo['updated_at']);
        }
        $difference =  $modifiedTime->getTimestamp() - $currentTime->getTimestamp();
        if($toDo['duration'] > $difference) {
            return ($toDo['duration'] - $difference);
        }else{
            return 0;
        }
    }
}
