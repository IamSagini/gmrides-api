<?php
namespace Database\Seeders;
use App\Models\PageContent;
use Illuminate\Database\Seeder;

class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'privacy' => ['title' => 'Privacy Policy', 'sections' => [
                ['h'=>'1. Introduction','p'=>['GM Rides ("we", "us", "our") operates a transport booking platform offering intercity travel, airport transfers, and private hire across Kenya. This Privacy Policy explains how we collect, use, store, and protect your personal information when you use our website and services.','We are committed to handling your data responsibly and in accordance with the Kenya Data Protection Act, 2019, and other applicable laws. By using GM Rides, you agree to the practices described in this policy.']],
                ['h'=>'2. Information We Collect','p'=>['We collect information you provide directly when you make a booking or create an account, including your name, email address, phone number, and travel details.','Payment processing is handled by our third-party providers (M-Pesa, Stripe). We do not store full card numbers or M-Pesa PINs.','If you sign in with Google, we receive your name and email from Google to create or access your account.','We may also collect limited technical information such as your browser type and general usage data.']],
                ['h'=>'3. How We Use Your Information','p'=>['We use your information to process and confirm bookings, send confirmations and travel updates by SMS and email, provide support, calculate loyalty discounts, and improve our services.','We do not sell your personal information to third parties.']],
                ['h'=>'4. Sharing Your Information','p'=>['We share information only as necessary: with payment processors, SMS providers, and transport operators to fulfil your bookings.','We may disclose information if required by law, or to protect the rights and safety of GM Rides, our users, or others.']],
                ['h'=>'5. Data Storage and Security','p'=>['We take reasonable measures to protect your information. Passwords are stored in encrypted (hashed) form and are never visible to us.','No method of transmission or storage is completely secure, and we cannot guarantee absolute security.']],
                ['h'=>'6. Your Rights','p'=>['Under the Kenya Data Protection Act, 2019, you have the right to access, correct, delete, and object to processing of your personal data.','To exercise these rights, contact us using the details below.']],
                ['h'=>'7. Data Retention','p'=>['We retain your booking and account information for as long as your account is active or as needed to provide services, comply with legal obligations, and resolve disputes.']],
                ['h'=>'8. Cookies','p'=>['Our website uses minimal local storage to remember your preferences (such as currency and login session). We do not use invasive tracking cookies for advertising.']],
                ['h'=>'9. Changes to This Policy','p'=>['We may update this policy from time to time. Changes will be posted here with an updated date. Continued use constitutes acceptance.']],
                ['h'=>'10. Contact Us','p'=>['Questions about this Privacy Policy? Contact us at hello@gmrides.net. GM Rides is based in Nairobi, Kenya.']],
            ]],
            'terms' => ['title' => 'Terms of Service', 'sections' => [
                ['h'=>'1. Acceptance of Terms','p'=>['By accessing or using GM Rides, you agree to be bound by these Terms of Service. If you do not agree, please do not use our platform.']],
                ['h'=>'2. Our Services','p'=>['GM Rides is a transport booking platform for intercity travel, airport transfers, and private hire across Kenya. We facilitate bookings and payments between you and transport operators.','Route, fare, and availability information may change and is subject to confirmation at booking.']],
                ['h'=>'3. Bookings and Payments','p'=>['You agree to provide accurate information and pay the fare shown at checkout. Payment is processed via M-Pesa, card (Stripe), and other methods as available.','A booking is confirmed only once payment succeeds. You receive a confirmation with a booking reference by SMS and on screen.','Fares display in your selected currency; converted amounts may vary slightly due to exchange rates.']],
                ['h'=>'4. Loyalty Discounts','p'=>['Returning customers may qualify for loyalty discounts based on confirmed trips. Discounts apply automatically when eligible and may change at our discretion.']],
                ['h'=>'5. Cancellations and Changes','p'=>['Cancellation and refund eligibility are governed by our Refund Policy. Some bookings may be non-refundable or subject to fees.']],
                ['h'=>'6. User Responsibilities','p'=>['Use GM Rides lawfully and keep your account credentials secure. Arrive on time; GM Rides and operators are not liable for missed departures due to late arrival.']],
                ['h'=>'7. Limitation of Liability','p'=>['GM Rides acts as a booking platform and is not liable for delays, cancellations, loss, injury, or damages from the transport services themselves, except where required by law.','Our total liability for any booking claim shall not exceed the amount paid for that booking.']],
                ['h'=>'8. Account Suspension','p'=>['We may suspend or terminate accounts that violate these terms, engage in fraud, or misuse the platform.']],
                ['h'=>'9. Changes to These Terms','p'=>['We may update these terms from time to time. Changes are posted here with a revised date. Continued use constitutes acceptance.']],
                ['h'=>'10. Governing Law','p'=>['These terms are governed by the laws of Kenya. Disputes are subject to the jurisdiction of the Kenyan courts.']],
                ['h'=>'11. Contact','p'=>['Questions? Contact us at hello@gmrides.net. GM Rides is based in Nairobi, Kenya.']],
            ]],
            'refund' => ['title' => 'Refund Policy', 'sections' => [
                ['h'=>'1. Overview','p'=>['This Refund Policy explains how cancellations and refunds work for GM Rides bookings. It should be read together with our Terms of Service.']],
                ['h'=>'2. Cancellations by You','p'=>['Refund eligibility depends on how far in advance you cancel:','More than 24 hours before departure: full refund, less any non-refundable processing fees.','Between 6 and 24 hours before departure: partial refund of up to 50% of the fare.','Less than 6 hours before departure, or no-shows: generally non-refundable.','These are general guidelines; specific operators or fare types may differ, as indicated at booking.']],
                ['h'=>'3. Cancellations by Us or the Operator','p'=>['If GM Rides or the operator cancels a trip, you are entitled to a full refund or free rebooking on an alternative trip where available.']],
                ['h'=>'4. How Refunds Are Processed','p'=>['Approved refunds return to the original payment method. M-Pesa refunds go to the paying number; card refunds go to the original card via Stripe.','Refunds are typically processed within 5 to 10 business days, depending on your payment provider.']],
                ['h'=>'5. Non-Refundable Items','p'=>['Certain charges may be non-refundable, including processing fees and clearly marked non-refundable or promotional fares.']],
                ['h'=>'6. How to Request a Refund','p'=>['Contact us at hello@gmrides.net with your booking reference and reason. We will review and confirm eligibility in line with this policy.']],
                ['h'=>'7. Changes to This Policy','p'=>['We may update this policy from time to time. The policy in effect at the time of your booking applies to that booking.']],
                ['h'=>'8. Contact','p'=>['Questions about refunds? Contact us at hello@gmrides.net. GM Rides is based in Nairobi, Kenya.']],
            ]],
        ];
        foreach ($pages as $slug => $d) {
            PageContent::updateOrCreate(['slug' => $slug], ['title' => $d['title'], 'sections' => $d['sections']]);
        }
    }
}
