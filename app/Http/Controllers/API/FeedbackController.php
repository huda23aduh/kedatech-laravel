<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\Feedback;
use App\Models\ReportFeedback;
use DB;
use Validator;

class FeedbackController extends BaseController
{
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string',
                'details' => 'required|string',
            ]);

            if($validator->fails()){
                return $this->sendError('Validation Error.', $validator->errors());
            }

            $feedback = Feedback::create([
                'title' => $request->input('title'),
                'details' => $request->input('details'),
                'created_by' => Auth::guard('api')->user()->id,
            ]);

            $feedback->save();
            DB::commit();

            return $this->sendResponse($feedback, 'created successfully.');

        } catch (\Exception $e) {
            if (env('APP_DEBUG') == TRUE) {
                return $e->getMessage();
            } else {
                return $this->sendError('something went wrong.', ['error'=>'storeFeedback']);
            }
        }
    }

    public function report(Request $request)
    {
        try {
            $findFeedback = Feedback::find($request->input('feedback_id'));

            if(is_null($findFeedback)) {
                return $this->sendError('Data not found.', 'Data not found.');
            }

            $findReportFeedback = ReportFeedback::where('feedback_id', $request->input('feedback_id'))
                ->where('reported_by', Auth::guard('api')->user()->id)
                ->first();

            $reportFeedback = new ReportFeedback;
            if (is_null($findReportFeedback)) {

                $validator = Validator::make($request->all(), [
                    'feedback_id' => 'required|integer'
                ]);
                if($validator->fails()){
                    return $this->sendError('Validation Error.', $validator->errors());
                }

                $reportFeedback->feedback_id = $request->input('feedback_id');
                $reportFeedback->reported_by = Auth::guard('api')->user()->id;

                $reportFeedback->save();
                DB::commit();
            } else {
                $reportFeedback = $findReportFeedback;
            }

            return $this->sendResponse($reportFeedback, 'reported successfully.');


        } catch (\Exception $e) {
            if (env('APP_DEBUG') == TRUE) {
                return $e->getMessage();
            } else {
                return $this->sendError('something went wrong.', ['error'=>'reportFeedback']);
            }
        }
    }
}
