<?php

namespace App\Http\Controllers\Sso;

use Input;
use Session;
use Response;
use App\Models\Sso\Account;
use App\Models\Sso\Token;
use App\Models\Mship\Account as MemberAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Security extends \App\Http\Controllers\BaseController {

    private $_ssoAccount;

    public function postGenerate() {
        if ($x = $this->security()) {
            return $x;
        }

        // Return URL must be provided!
        if(!Input::get("return_url", false)){
            return Response::json(array("status" => "error", "error" => "NO_RETURN_URL"));
        }

        $token = new Token();
        $_t = sha1(uniqid($this->_ssoAccount->username, true));
        $token->token = md5($_t . $this->_ssoAccount->api_key_private);
        $token->return_url = Input::get("return_url");
        $token->created_at = \Carbon\Carbon::now()->toDateTimeString();
        $token->expires_at = \Carbon\Carbon::now()->addMinutes(30)->toDateTimeString();
        $this->_ssoAccount->tokens()->save($token);

        // We want to return the token to the user for later use in their requests.
        return Response::json(array("status" => "success", "token" => $_t, "timestamp" => strtotime($token->created_at)));
    }

    public function postDetails() {
        if ($x = $this->security()) {
            return $x;
        }

        // Did we receive a token?  If we didn't get rid of them!
        if(!Input::get("access_token", false)){
            die("SOME GENERIC ERROR");
        }

        // Check expired/invalid
        $accessToken = Input::get("access_token");
        try {
            $accessToken = Token::tokenValue($accessToken)->valid()->firstOrFail();
        } catch (ModelNotFoundException $e) {
            die("TOKEN NOT FOUND");
        }

        $accessToken->expires_at = \Carbon\Carbon::now()->toDateTimeString();
        $accessToken->save();

        // Create the response...
        $account = MemberAccount::find($accessToken->account_id);
        $return = array();
        $return["cid"] = $account->id;
        $return["name_first"] = $account->name_first;
        $return["name_last"] = $account->name_last;
        $return["name_full"] = $account->name;

        // Let's get their email for this system (if they've got one set).
        $return["email"] = $account->email;

        $ssoEmailAssigned = $account->ssoEmails->filter(function($ssoemail) use ($accessToken) {
            return $ssoemail->sso_account_id == $accessToken->sso_account_id;
        })->values();

        if($ssoEmailAssigned && count($ssoEmailAssigned) > 0){
            \Log::info($ssoEmailAssigned);
            $return['email'] = $ssoEmailAssigned[0]->email->email;
        }

        $return["atc_rating"] = $account->qualification_atc->vatsim;
        $return["atc_rating_human_short"] = $account->qualification_atc->name_small;
        $return["atc_rating_human_long"] = $account->qualification_atc->name_long;
        $return["atc_rating_date"] = $account->qualification_atc->created_at->toDateTimeString();

        $return["pilot_ratings_bin"] = 0;
        $return["pilot_ratings"] = array();
        if(count($account->qualifications_pilot) < 1){
            $return["pilot_ratings"][]= 0;
            $return["pilot_ratings_human_short"][] = "NA";
            $return["pilot_ratings_human_long"][] = "None Awarded";
        } else {
            foreach($account->qualifications_pilot as $qual){
                $e = array();
                $e["rating"] = $qual->vatsim;
                $e["human_short"] = $qual->name_small;
                $e["human_long"] = $qual->name_long;
                $e["date"] = $qual->created_at->toDateTimeString();
                $return["pilot_ratings"][] = (array) $e;
                $return["pilot_ratings_bin"]+= $qual->vatsim;
            }
        }
        $return["pilot_ratings_bin"] = decbin($return["pilot_ratings_bin"]);

        $return["admin_ratings"] = array();
        foreach($account->qualifications_admin as $qual){
            $e = array();
            $e["rating"] = $qual->vatsim;
            $e["human_short"] = $qual->name_small;
            $e["human_long"] = $qual->name_long;
            $e["date"] = $qual->created_at->toDateTimeString();
            $return["admin_ratings"][] = (array) $e;
        }

        $return["training_pilot_ratings"] = array();
        foreach($account->qualifications_pilot_training as $qual){
            $e = array();
            $e["rating"] = $qual->vatsim;
            $e["human_short"] = $qual->name_small;
            $e["human_long"] = $qual->name_long;
            $e["date"] = $qual->created_at->toDateTimeString();
            $return["training_pilot_ratings"][] = (array) $e;
        }

        $return["training_atc_ratings"] = array();
        foreach($account->qualifications_atc_training as $qual){
            $e = array();
            $e["rating"] = $qual->vatsim;
            $e["human_short"] = $qual->name_small;
            $e["human_long"] = $qual->name_long;
            $e["date"] = $qual->created_at->toDateTimeString();
            $return["training_atc_ratings"][] = (array) $e;
        }

        $return["account_state"] = $account->all_states;
        $return["account_state_current"] = $account->current_state->label;
        $return["account_status"] = $account->status;
        $return["is_invisible"] = boolval($account->is_invisible);

        $return["is_banned"] = boolval($account->is_banned);
        $return["ban_info"] = ($account->is_banned ? $account->bans->first() : null);

        $return["is_inactive"] = boolval($account->is_inactive);
        $return["experience"] = $account->experience;
        $return["reg_date"] = $account->joined_at->toDateTimeString();
        $return["impersonation"] = Session::get("auth_override", false);

        // We want to return the token to the user for later use in their requests.
        return Response::json(array("status" => "success", "data" => $return));
    }

    private function security() {
        if (!Input::get("username", false)) {
            return Response::json(array("status" => "error", "error" => "NO_USERNAME"));
        }

        if (!Input::get("apikey_pub", false)) {
            return Response::json(array("status" => "error", "error" => "NO_APIKEY_PUB"));
        }

        // Authenticate....
        try {
            $this->_ssoAccount = Account::where("username", "=", Input::get("username"))
                    ->where("api_key_public", "=", Input::get("apikey_pub"))
                    ->first();
        } catch (Exception $e) {
            return Response::json(array("status" => "error", "error" => "INVALID_CREDENTIALS"));
        }

        if (is_null($this->_ssoAccount)) {
            return Response::json(array("status" => "error", "error" => "INVALID_CREDENTIALS"));
        }
    }

}
