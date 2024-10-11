<?php

namespace App\Http\Controllers;

use App\Models\TokenSisModel;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\RFCValidation;
use Exception;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function checatoken($token)
    {
        $token1 = new TokenSisModel();

        $guarda = $token1::where('token', $token)->get();

        if ($guarda->isempty()) {
            return false;
        } else {
            if ($guarda[0]['parceiro'] === 'PLAY MÓVEL') {
                return true;
            }

            if ($guarda[0]['bloqueio'] == 1) {
                return false;
            } else {
                return true;
            }
        }
    }

    public function validacpf($number)
    {
        $cpf = preg_replace('/[^0-9]/', "", $number);

        if (strlen($cpf) != 11 || preg_match('/([0-9])\1{10}/', $cpf)) {
            return response('CPF Inválido', 590);
        }

        $number_quantity_to_loop = [9, 10];

        foreach ($number_quantity_to_loop as $item) {

            $sum = 0;
            $number_to_multiplicate = $item + 1;

            for ($index = 0; $index < $item; $index++) {

                $sum += $cpf[$index] * ($number_to_multiplicate--);
            }

            $result = (($sum * 10) % 11);

            if ($cpf[$item] != $result) {
                return response('CPF Inválido', 590);
            }
        }

        return true;
    }

    function ValidaEmail($endereço)
    {
        $validator = new EmailValidator();
        return $validator->isValid($endereço, new RFCValidation());
    }

    // public function enviarEmailEsqueciSenha($name, $template, $mensagem, $token, $toemail, $toname, $subject, $fromemail, $fromname, $empresa, $link, $cnpj)
    // {
    //     $company = new CompanyModel();
    //     $companyinfo = $company::where('tradename', $empresa)->first();

    //     if(!$companyinfo){
    //         $revendedorPj = new RevendedorPjModel();
    //         $revendedor = $revendedorPj::where('cnpj', $cnpj)->first();
    //         $companyname = $revendedor->companyname;            
    //         $logo = '';
    //     } else {
    //         $companyname = $companyinfo->companyname;            
    //         if ($companyinfo->logotipo == null) {
    //             $logo = '';
    //         } else {
    //             $logo = stream_get_contents($companyinfo->logotipo);
    //         }
    //     }

    //     $data = array(
    //         'name' => $name,
    //         'email' => $toemail,
    //         'toemail' => $toemail,
    //         'toname' => $toname,
    //         'subject' => $subject,
    //         'fromemail' => $fromemail,
    //         'fromname' => $fromname,
    //         'mensagem' => $mensagem,
    //         'token' => $token,
    //         'companylogo' => $logo,
    //         'companyname' => $companyname,
    //         'link' => $link,
    //     );
    //     // Path or name to the blade template to be rendered
    //     $template_path = $template;

    //     try {
    //         Mail::send($template_path, $data, function ($message) use ($data) {
    //             // Set the receiver and subject of the mail.
    //             $message->to($data['toemail'], $data['toname'])->subject($data['subject']);
    //             // Set the sender

    //             $message->from($data['fromemail'], $data['fromname']);
    //         });
    //     } catch (Exception $e) {
    //         return response('Erro ao enviar o e-mail de redefinição de senha: ' . $e->getMessage(), 500);
    //     }

    //     return "Email enviado, verifique sua caixa de entrada";
    // }

    public function enviarEmailEsqueciSenha($name, $template, $mensagem, $token, $toemail, $toname, $subject, $fromemail, $fromname, $link, $cnpj)
    {
        $data = array(
            'name' => $name,
            'email' => $toemail,
            'toemail' => $toemail,
            'toname' => $toname,
            'subject' => $subject,
            'fromemail' => $fromemail,
            'fromname' => $fromname,
            'mensagem' => $mensagem,
            'token' => $token,
            'link' => $link,
        );
        // Path or name to the blade template to be rendered
        $template_path = $template;

        try {
            Mail::send($template_path, $data, function ($message) use ($data) {
                // Set the receiver and subject of the mail.
                $message->to($data['toemail'], $data['toname'])->subject($data['subject']);
                // Set the sender

                $message->from($data['fromemail'], $data['fromname']);
            });
        } catch (Exception $e) {
            return response('Erro ao enviar o e-mail de redefinição de senha: ' . $e->getMessage(), 500);
        }

        return "Email enviado, verifique sua caixa de entrada";
    }
}
