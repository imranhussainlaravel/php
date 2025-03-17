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
                body, table, td {
                    font-family: Arial, sans-serif;
                    background-color: #000;
                    color: #fff;
                    text-align: center;
                    margin: 0;
                    padding: 0;
                }
                table {
                    max-width: 600px;
                    margin: auto;
                    background: #111;
                    border-radius: 8px;
                    box-shadow: 0px 4px 10px rgba(255, 255, 255, 0.1);
                    padding: 20px;
                }
                h1 {
                    font-size: 48px;
                    font-weight: bold;
                    color: #3c6fb1;
                    margin-bottom: 20px;
                    text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
                    text-align: center;
                }
                p {
                    font-size: 18px;
                    margin: 20px 0;
                    text-align: center;
                    line-height: 1.6;
                }
                .details {
                    text-align: left;
                    margin: 20px auto;
                    background-color: #fff;
                    color: #000;
                    padding: 20px;
                    border-radius: 8px;
                    max-width: 550px;
                    box-shadow: 0px 2px 8px rgba(255,255,255,0.1);
                }
                .details .field {
                    margin-bottom: 15px;
                    font-size: 16px;
                }
                .details .field strong {
                    color: #4CAF50;
                }
                .button-container {
                    text-align: center; /* Ensures centering */
                    padding: 20px 0;
                }
                .button {
                    display: inline-block;
                    background-color: #3c6fb1;
                    color: #ffffff;
                    padding: 12px 20px;
                    font-size: 18px;
                    font-weight: bold;
                    text-decoration: none;
                    border-radius: 6px;
                    margin-top: 20px;
                    transition: 0.3s;
                    text-align: center;
                }
                .button:hover {
                    background-color: #2a4f7b;
                }
                .footer {
                    margin-top: 30px;
                    font-size: 14px;
                    color: #bbb;
                    text-align: center;
                }
                @media screen and (max-width: 480px) {
                    table {
                        width: 90%;
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
            <table width='100%' border='0' cellspacing='0' cellpadding='0' align='center'>
                <tr>
                    <td align='center'>
                        <table width='600' border='0' cellspacing='0' cellpadding='20' align='center'>
                            <tr>
                                <td align='center'>
                                    <h1>Thank You.</h1>
                                    <p>We have received your details and will get back to you soon. Below is the information you provided:</p>
                                </td>
                            </tr>
                            <tr>
                                <td align='center'>
                                    <table class='details' width='100%' cellpadding='10' cellspacing='0' border='0' align='center'>
                                        <tr>
                                            <td><strong>Product Name:</strong> <span style='font-weight:bold;'>{$details['product_name']}</span></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Quantity:</strong> {$details['quantity']}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Color:</strong> {$details['color']}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dimensions:</strong> {$details['length']} x {$details['width']} x {$details['depth']} ({$details['measurement_unit']})</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Description:</strong> {$details['description']}</td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align='center'>
                                    <div class='button-container'>
                                        <a href='https://nexonpackaging.com' class='button'>Contact Us</a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td align='center' class='footer'>
                                    <p>Need help? <a href='mailto:sales@nexonpackaging.com' style='color: #4CAF50;'>Email Support</a></p>
                                </td>
                            </tr>
                        </table>
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
            'quantity' => '',
            'color' => '',
            'measurements' => '',
            'description' => $request->description,
        ]);

        $response = [
            'message' => 'Contact details send successfully.',
            'status' => 200,
            'success' => true,
        ];
       

    }
}
