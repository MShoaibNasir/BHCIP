<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;

class ContentController extends Controller
{
        
    public function privacy_policy(Request $request){
        $privacyPolicy = $this->getDummyPrivacyPolicy();
        if($privacyPolicy){
        return response()->json(['privacy_policy' => $privacyPolicy]);
        }else{
            return response()->json(['Message' => 'Privacy Policy Data Not found!']);
        }
    }
    public function terms_and__condition(Request $request){
        $terms_and_condition = $this->getDummyTermsCondition();
        if($terms_and_condition){
        return response()->json(['privacy_policy' => $terms_and_condition]);
        }else{
            return response()->json(['Message' => 'Privacy Policy Data Not found!']);
        }
    }
    
    
    
    
     private function getDummyPrivacyPolicy()
    {
        
        $dummyPrivacyPolicy = "At Innovative Technology, we prioritize the privacy and security of our users. This Privacy Policy outlines how we collect, use, and safeguard your personal information.Personal Information: We may collect personal information such as names, contact details, and professional credentials to personalize user accounts and ensure a tailored learning experience.Usage Data: We collect data on user interactions within the app, including module completion, assessment scores, and participation in discussion forums, for analytical purposes Personalized Learning: Collected information is used to customize learning paths, recommend relevant content, and enhance the overall user experience.•	App Improvement: Aggregated and anonymized data may be used to improve app functionality, identify trends, and develop new features.•	We employ industry-standard security measures to protect your personal information from unauthorized access, disclosure, alteration, and destruction.•	The app may integrate third-party services for analytics and user engagement. These services adhere to their own privacy policies, and we recommend reviewing them for a comprehensive understanding.•	Users have the right to review, update, or delete their personal information. You can manage your preferences within the app settings.•	We may send notifications, updates, or important announcements to users via the app. Users can opt-out of non-essential communications.This Privacy Policy may be updated to reflect changes in data processing practices. Users will be notified of any significant updates.";
return $dummyPrivacyPolicy;
    }
    
    
    private function getDummyTermsCondition(){
$content='Welcome to the Innovative Technology mobile app, designed to revolutionize the capacity building, assessment, and training of healthcare workers in various medical facilities. This application is specifically made for doctors, consultants, nurses, Lady Health Visitors (LHVs), Lady Health Workers (LHWs), Healthcare leaders, Delivery Attendant Instructors (DAIs), and paramedic staff, providing a comprehensive platform to enhance their skills and knowledge in the dynamic field of healthcare.
: The Innovative Technology app offers personalized learning paths for each healthcare professional, ensuring that the content is relevant and meets individual needs.Access a diverse range of multimedia resources, including interactive modules, video lectures, Quizzes, to facilitate engaging and effective learning experiences.: Regular assessments and quizzes are integrated into the app to gauge the progress of healthcare workers, identify areas for improvement.Keep track of your progress in real-time with our intuitive dashboard. Monitor completed modules, assessment scores, and overall performance at your convenience.Stay up-to-date with the latest advancements in healthcare through regularly updated content. Our team of experts ensures that the app reflects the most current and relevant information in the field.';
return $content;
    }    
}
