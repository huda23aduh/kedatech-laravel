<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;
use App\Models\RoomMember;
use App\Models\Room;
use App\Models\User;
use App\Models\Message;

class MessageController extends BaseController
{
    public function index()
    {
        $roomIds = RoomMember::where('user_id', Auth::guard('api')->user()->id)->pluck('room_id');
        if ($roomIds->count() > 0 ) {
            $roomIdsImplode =  $roomIds->implode(', ');
            $chat = DB::select("SELECT * FROM messages WHERE msg_room_id IN (" . $roomIdsImplode . " ) AND id IN (SELECT MAX(id) FROM messages GROUP BY msg_room_id) ORDER BY created_at DESC");

            return $this->sendResponse($chat, 'chat retrieved successfully.');

        } else {
            return $this->sendResponse([], 'chat retrieved successfully.');
        }
    }

    public function allChat()
    {
        $chat = DB::select("SELECT * FROM messages WHERE id IN (SELECT MAX(id) FROM messages GROUP BY msg_room_id) ORDER BY created_at DESC");

        return $this->sendResponse($chat, 'chat retrieved successfully.');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|int',
                'content' => 'required|string',
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $checkOpponent = User::find($request->input('receiver_id'));
            if (is_null($checkOpponent)) {
                return $this->sendError('Opponent not found.', 'Opponent not found.');
            }

            $roomSender = RoomMember::where('user_id', Auth::guard('api')->user()->id)->get();
            $roomOpponent = RoomMember::where('user_id', $checkOpponent->id)->pluck('room_id');

            $roomSenderOpponent = $roomSender->whereIn('room_id', $roomOpponent)->first();

            $roomId = 0;
            if ( is_null($roomSenderOpponent)) {
                $room = Room::create([
                    'title' => Auth::guard('api')->user()->id.'_'.$checkOpponent->id,
                    'description' => 'description of room '.Auth::guard('api')->user()->id.'_'.$checkOpponent->id,
                    'room_type' => 'single',
                ]);
                $room->save();
                $roomId = $room->id;

                $roomMemberSender = RoomMember::create([
                    'room_id' => $roomId,
                    'user_id' => Auth::guard('api')->user()->id,
                ]);
                $roomMemberSender->save();

                $roomMemberOpponnet = RoomMember::create([
                    'room_id' => $roomId,
                    'user_id' => $checkOpponent->id,
                ]);
                $roomMemberOpponnet->save();

            } else {
                $roomId = $roomSenderOpponent->room_id;
            }

            $msg = Message::create([
                'content' => $request->input('content'),
                'msg_room_id' => $roomId,
                'sender_id' => Auth::guard('api')->user()->id,
            ]);
            $msg->save();

            DB::commit();


            return $this->sendResponse($msg, 'message sent successfully.');

        } catch (\Exception $e) {
            if (env('APP_DEBUG') == TRUE) {
                return $e->getMessage();
            } else {
                return $this->sendError('something went wrong.', ['error'=>'sendMessage']);
            }
        }
    }
}
