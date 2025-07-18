<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Mail;


class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'product_name' => 'nullable',
            'quantity' => 'nullable',
            'color' => 'nullable',
            'length' => 'nullable',
            'width' => 'nullable',
            'depth' => 'nullable',
            'measurement_unit' => 'nullable',
            'description' => 'nullable',
            'url' => 'nullable',

          
        ]);

        $measurements = array_filter([
            $request->length,
            $request->width,
            $request->depth
        ]); 
    
        $measurementString = $measurements ? implode(' x ', $measurements) : null;
        if ($measurementString && $request->measurement_unit) {
            $measurementString .= " ({$request->measurement_unit})";
        }

        RequestModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'color' => $request->color,
            'measurements' => $measurementString,
            'description' => $request->description,
            'url' => $request->url,
        ]);

        
        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => 'New Custom Quote Request',
            'phone' => $request->phone,
            'product_name' => $request->product_name ?? 'N/A', // Handle null values
            'quantity' => $request->quantity ?? 'N/A',
            'color' => $request->color ?? 'N/A',
            'length' => $request->length ?? 'N/A',
            'width' => $request->width ?? 'N/A',
            'depth' => $request->depth ?? 'N/A',
            'measurement_unit' => $request->measurement_unit ?? 'N/A',
            'description' => $request->description ?? 'N/A',
        ];

        // Create the HTML content
        // $htmlContent = "
        // <html>
        // <head>
        //     <style>
        //         body {
        //             font-family: Arial, sans-serif;
        //             background-color: #000;
        //             color: #fff;
        //             text-align: center;
        //             padding: 40px;
        //         }
        //         h1 {
        //             font-size: 48px;
        //             font-weight: bold;
        //             color: #fff;
        //             margin-bottom: 20px;
        //             text-align: center;
        //             text-shadow: 
        //                 -1px -1px 0 #000,  /* Top left */
        //                 1px -1px 0 #000,   /* Top right */
        //                 -1px 1px 0 #000,   /* Bottom left */
        //                 1px 1px 0 #000; 
        //         }
        //         p {
        //             font-size: 20px;
        //             margin: 20px 0;
        //             text-align: center;
        //         }
        //         .details {
        //             text-align: left;
        //             margin: 20px auto;
        //             background-color: #fff;
        //             color: #000;
        //             padding: 20px;
        //             border-radius: 8px;
        //             max-width: 600px;
        //             box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
        //         }
        //         .details .field {
        //             margin-bottom: 15px;
        //         }
        //         .details .field strong {
        //             color: #4CAF50;
        //         }
        //     </style>
        // </head>
        // <body>
        //     <h1 style='font-size: 48px; font-weight: bold; color: #3c6fb1; margin-bottom: 20px; '>
        //         Thank you.
        //     </h1>

        //     <p>We have received your details and will get back to you soon. Below is the information you provided:</p>
        //     <div class='details'>
        //         <div class='field'>
        //             <strong>Product Name:</strong> <span style='font-weight:bold;'>{$details['product_name']}</span>
        //         </div>
        //         <div class='field'>
        //             <strong>Quantity:</strong> {$details['quantity']}
        //         </div>
        //         <div class='field'>
        //             <strong>Color:</strong> {$details['color']}
        //         </div>
        //         <div class='field'>
        //             <strong>Dimensions:</strong> {$details['length']} x {$details['width']} x {$details['depth']} ({$details['measurement_unit']})
        //         </div>
        //         <div class='field'>
        //             <strong>Description:</strong> {$details['description']}
        //         </div>
        //     </div>
        // </body>
        // </html>
        // ";
        $htmlContentteam = "
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                    text-align: center;
                    padding: 20px;
                }
                h1 {
                    font-size: 32px;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 20px;
                }
                p {
                    font-size: 18px;
                    margin: 20px 0;
                    text-align: center;
                }
                .details {
                    text-align: left;
                    margin: 0 auto;
                    background-color: #fff;
                    color: #333;
                    padding: 20px;
                    border-radius: 8px;
                    max-width: 600px;
                    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                }
                .details .field {
                    margin-bottom: 15px;
                }
                .details .field strong {
                    color: #4CAF50;
                }
            </style>
        </head>
        <body>
            <h1>New Quote Request from Customer</h1>
            <p>Here are the details submitted by the customer:</p>
            <div class='details'>
            <div class='field'>
                    <strong>Name:</strong> <span>{$details['name']}</span>
                </div>
                <div class='field'>
                    <strong>Customer Email:</strong> <span>{$details['email']}</span>
                </div>
                <div class='field'>
                    <strong>Phone Number:</strong> <span>{$details['phone']}</span>
                </div>
                <div class='field'>
                    <strong>Product Name:</strong> <span>{$details['product_name']}</span>
                </div>
                <div class='field'>
                    <strong>Quantity:</strong> <span>{$details['quantity']}</span>
                </div>
                <div class='field'>
                    <strong>Color:</strong> <span>{$details['color']}</span>
                </div>
                <div class='field'>
                    <strong>Dimensions:</strong> <span>{$details['length']} x {$details['width']} x {$details['depth']} ({$details['measurement_unit']})</span>
                </div>
                <div class='field'>
                    <strong>Description:</strong> <span>{$details['description']}</span>
                </div>
            </div>
        </body>
        </html>
        ";     
        $htmlContent = "
        <html>
        <head>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #ccc;
                    color: #fff;
                    text-align: center;
                    padding: 0;
                    margin: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #e0e0e0;
                    border-radius: 8px;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    text-align: center;
                }
                h1 {
                    font-size: 48px;
                    font-weight: bold;
                    color: #f0644b;
                    margin-bottom: 20px;
                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                }
                p {
                    font-size: 18px;
                    margin: 20px 0;
                    text-align: center;
                    line-height: 1.6;
                    color: #333;
                }
                .details {
                    text-align: left;
                    margin: 20px auto;
                    background-color: #fff;
                    color: #000;
                    padding: 20px;
                    border-radius: 8px;
                    max-width: 550px;
                    box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
                }
                .details .field {
                    margin-bottom: 15px;
                    font-size: 16px;
                }
                .details .field strong {
                    color: #4CAF50;
                }
                .button-container {
                    text-align: center; 
                }
                .button {
                    display: inline-block;
                    background-color: #f0644b;
                    color: #ffffff !important;
                    padding: 12px 20px;
                    font-size: 18px;
                    font-weight: bold;
                    text-decoration: none !important;
                    border-radius: 6px;
                    margin-top: 20px;
                    transition: 0.3s;
                }
                .button:hover {
                    background-color: #d9534f;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 14px;
                    color: #333;
                }
                .footer a {
                    color: #f0644b;
                    text-decoration: none;
                }
                .footer a:hover {
                    text-decoration: underline;
                }
                @media screen and (max-width: 480px) {
                    .email-container {
                        padding: 15px;
                    }
                    h1 {
                        font-size: 36px;
                    }
                    p {
                        font-size: 16px;
                    }
                    .details {
                        padding: 15px;
                    }
                    .details .field {
                        font-size: 14px;
                    }
                    .button {
                        font-size: 16px;
                        padding: 10px 15px;
                    }
                }
            </style>
        </head>
        <body>
            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                <tr>
                    <td align='center'>
                        <div class='email-container'>
                            <h1>Thank You.</h1>
                            <p>We have received your details and will get back to you soon. Below is the information you provided:</p>

                            <div class='details'>
                                <div class='field'><strong>Product Name:</strong> <span style='font-weight:bold;'>{$details['product_name']}</span></div>
                                <div class='field'><strong>Quantity:</strong> {$details['quantity']}</div>
                                <div class='field'><strong>Color:</strong> {$details['color']}</div>
                                <div class='field'><strong>Dimensions:</strong> {$details['length']} x {$details['width']} x {$details['depth']} ({$details['measurement_unit']})</div>
                                <div class='field'><strong>Description:</strong> {$details['description']}</div>
                            </div>

                            <div class='button-container'>
                                <a href='https://nexonpackaging.com' class='button'>Contact Us</a>
                            </div>

                            <div class='footer'>
                                <p>Need help? <a href='mailto:sales@nexonpackaging.com'>Email Support</a></p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";


        $teamemail = 'sales@nexonpackaging.com';
        $teamsubject = 'New Custom Quote by customer';

        try {
            Mail::html($htmlContent, function ($message) use ($details) {
                $message->to($details['email'])
                        ->subject($details['subject']);
            });
            Mail::html($htmlContentteam, function ($message) use ($details, $teamemail, $teamsubject) {
                $message->to($teamemail)
                        ->subject($teamsubject);
            });
            // print_r("Email sent successfully");exit();
            // return back()->with('success', 'Email sent successfully!');
            // return redirect()->route('thank.you')->with('success', 'Email sent successfully!');
            $response = [
                'message' => 'Email send successfully.',
                'status' => 200,
                'success' => true,
            ];
    
            return response()->json($response);
        } catch (\Exception $e) {
            // Log the error for debuggings
            \Log::error('Email sending failed: ' . $e->getMessage());
            // print_r($e->getMessage());exit();

            // return back()->with('error', 'Email sending failed!');
            $response = [
                'message' => 'Email not send.',
                'status' => 200,
                'success' => false,
            ];
    
            return response()->json($response);
        }

    }
    public function contact_us(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'description' => 'nullable',    
        ]);

       

        RequestModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'product_name' => '',
            'quantity' => '0',
            'color' => '',
            'type' => 'contact_us',
            'measurements' => '',
            'description' => $request->description,
        ]);

        $response = [
            'message' => 'Contact details send successfully.',
            'status' => 200,
            'success' => true,
        ];
        return response()->json($response);
       

    }
    public function subscribe_us(Request $request){
        $request->validate([
            'email' => 'required',
        ]);

       

        RequestModel::create([
            'name' => 'name',
            'email' => $request->email,
            'phone' => '',
            'product_name' => '',
            'color' => '',
            'type' => 'subscribe',
            'measurements' => '',
            'description' => '',
        ]);

        $response = [
            'message' => 'Contact details send successfully.',
            'status' => 200,
            'success' => true,
        ];
       
        return response()->json($response);
    }
    public function beatmyquote(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'product_name' => 'nullable',
            'quantity' => 'nullable',
            'color' => 'nullable',
            'length' => 'nullable',
            'width' => 'nullable',
            'depth' => 'nullable',
            'measurement_unit' => 'nullable',
            'description' => 'nullable',
            'quote_details' => 'nullable',
            'delivery_charges' => 'nullable',
            'othercharges' => 'nullable',
            'url' => 'nullable',
        ]);

        $measurements = array_filter([
            $request->length,
            $request->width,
            $request->depth
        ]); 
    
        $measurementString = $measurements ? implode(' x ', $measurements) : null;
        if ($measurementString && $request->measurement_unit) {
            $measurementString .= " ({$request->measurement_unit})";
        }

        $combinedDescription = 'description: ' . ($request->description ?? '') .
                       ', -----quote_details: ' . ($request->quote_details ?? '') .
                       ', -----delivery_charges: ' . ($request->delivery_charges ?? 0) .
                       ', -----othercharges: ' . ($request->othercharges ?? 0);

        RequestModel::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'product_name' => $request->product_name,
            'quantity' => $request->quantity,
            'color' => $request->color,
            'url' => $request->url,
            'measurements' => $measurementString,
            'description' => $combinedDescription,
        ]);

        
        $details = [
            'name' => $request->name,
            'email' => $request->email,
            'subject' => 'New Custom Quote Request',
            'phone' => $request->phone,
            'product_name' => $request->product_name ?? 'N/A', // Handle null values
            'quantity' => $request->quantity ?? 'N/A',
            'color' => $request->color ?? 'N/A',
            'length' => $request->length ?? 'N/A',
            'width' => $request->width ?? 'N/A',
            'depth' => $request->depth ?? 'N/A',
            'measurement_unit' => $request->measurement_unit ?? 'N/A',
            'description' => $request->description ?? 'N/A',
        ];

        
        
        $htmlContentteam = "
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    color: #333;
                    text-align: center;
                    padding: 20px;
                }
                h1 {
                    font-size: 32px;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 20px;
                }
                p {
                    font-size: 18px;
                    margin: 20px 0;
                    text-align: center;
                }
                .details {
                    text-align: left;
                    margin: 0 auto;
                    background-color: #fff;
                    color: #333;
                    padding: 20px;
                    border-radius: 8px;
                    max-width: 600px;
                    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
                }
                .details .field {
                    margin-bottom: 15px;
                }
                .details .field strong {
                    color: #4CAF50;
                }
            </style>
        </head>
        <body>
            <h1>New Quote Request from Customer</h1>
            <p>Here are the details submitted by the customer:</p>
            <div class='details'>
            <div class='field'>
                    <strong>Name:</strong> <span>{$details['name']}</span>
                </div>
                <div class='field'>
                    <strong>Customer Email:</strong> <span>{$details['email']}</span>
                </div>
                <div class='field'>
                    <strong>Phone Number:</strong> <span>{$details['phone']}</span>
                </div>
                <div class='field'>
                    <strong>Product Name:</strong> <span>{$details['product_name']}</span>
                </div>
                <div class='field'>
                    <strong>Quantity:</strong> <span>{$details['quantity']}</span>
                </div>
                <div class='field'>
                    <strong>Color:</strong> <span>{$details['color']}</span>
                </div>
                <div class='field'>
                    <strong>Dimensions:</strong> <span>{$details['length']} x {$details['width']} x {$details['depth']} ({$details['measurement_unit']})</span>
                </div>
                <div class='field'>
                    <strong>Description:</strong> <span>{$details['description']}</span>
                </div>
            </div>
        </body>
        </html>
        ";     
        $htmlContent = "
        <html>
        <head>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #ccc;
                    color: #fff;
                    text-align: center;
                    padding: 0;
                    margin: 0;
                }
                .email-container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: #e0e0e0;
                    border-radius: 8px;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                    padding: 20px;
                    text-align: center;
                }
                h1 {
                    font-size: 48px;
                    font-weight: bold;
                    color: #f0644b;
                    margin-bottom: 20px;
                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                }
                p {
                    font-size: 18px;
                    margin: 20px 0;
                    text-align: center;
                    line-height: 1.6;
                    color: #333;
                }
                .details {
                    text-align: left;
                    margin: 20px auto;
                    background-color: #fff;
                    color: #000;
                    padding: 20px;
                    border-radius: 8px;
                    max-width: 550px;
                    box-shadow: 0px 2px 8px rgba(0,0,0,0.1);
                }
                .details .field {
                    margin-bottom: 15px;
                    font-size: 16px;
                }
                .details .field strong {
                    color: #4CAF50;
                }
                .button-container {
                    text-align: center; 
                }
                .button {
                    display: inline-block;
                    background-color: #f0644b;
                    color: #ffffff !important;
                    padding: 12px 20px;
                    font-size: 18px;
                    font-weight: bold;
                    text-decoration: none !important;
                    border-radius: 6px;
                    margin-top: 20px;
                    transition: 0.3s;
                }
                .button:hover {
                    background-color: #d9534f;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 14px;
                    color: #333;
                }
                .footer a {
                    color: #f0644b;
                    text-decoration: none;
                }
                .footer a:hover {
                    text-decoration: underline;
                }
                @media screen and (max-width: 480px) {
                    .email-container {
                        padding: 15px;
                    }
                    h1 {
                        font-size: 36px;
                    }
                    p {
                        font-size: 16px;
                    }
                    .details {
                        padding: 15px;
                    }
                    .details .field {
                        font-size: 14px;
                    }
                    .button {
                        font-size: 16px;
                        padding: 10px 15px;
                    }
                }
            </style>
        </head>
        <body>
            <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                <tr>
                    <td align='center'>
                        <div class='email-container'>
                            <h1>Thank You.</h1>
                            <p>We have received your details and will get back to you soon. Below is the information you provided:</p>

                            <div class='details'>
                                <div class='field'><strong>Product Name:</strong> <span style='font-weight:bold;'>{$details['product_name']}</span></div>
                                <div class='field'><strong>Quantity:</strong> {$details['quantity']}</div>
                                <div class='field'><strong>Color:</strong> {$details['color']}</div>
                                <div class='field'><strong>Dimensions:</strong> {$details['length']} x {$details['width']} x {$details['depth']} ({$details['measurement_unit']})</div>
                                <div class='field'><strong>Description:</strong> {$details['description']}</div>
                            </div>

                            <div class='button-container'>
                                <a href='https://nexonpackaging.com' class='button'>Contact Us</a>
                            </div>

                            <div class='footer'>
                                <p>Need help? <a href='mailto:sales@nexonpackaging.com'>Email Support</a></p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";


        $teamemail = 'sales@nexonpackaging.com';
        $teamsubject = 'New Custom Quote by customer';

        try {
            Mail::html($htmlContent, function ($message) use ($details) {
                $message->to($details['email'])
                        ->subject($details['subject']);
            });
            Mail::html($htmlContentteam, function ($message) use ($details, $teamemail, $teamsubject) {
                $message->to($teamemail)
                        ->subject($teamsubject);
            });
           
            $response = [
                'message' => 'Email send successfully.',
                'status' => 200,
                'success' => true,
            ];
    
            return response()->json($response);
        } catch (\Exception $e) {
            // \Log::error('Email sending failed: ' . $e->getMessage());

            $response = [
                'message' => 'Email not send.',
                'status' => 200,
                'success' => false,
            ];
    
            return response()->json($response);
        }

    }
    
}
