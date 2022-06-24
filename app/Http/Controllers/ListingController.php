<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
   
    //get and show all listings
    public function index(){
        // dd(request('tag'));
        return view('listings.index',[
            'listings' => Listing::latest()->filter(request(['tag','search']))->paginate(4)
            // simplepaginate shows previous and next

            // if not usingtailwind run php vendor:publish select number then style 
        ]);
    }
    // show single listing
    public function show(Listing $listing){ 
        return view('listings.show',[
            'listing' => $listing 
            ]);
    }
    // show createform 
    public function create(){
        return view('listings.create');
    }

       // Store Listing Data
       public function store(Request $request) {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required',
            // 'user_id' => auth()->id()
            
        ]);

        if($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);

        return redirect('/')->with('message', 'Listing created successfully!');
    }


// show Edit form
public function edit(Listing $listing){
        return view('listings.edit',['listing'=>$listing]);
}

  // update listings data
  public function update(Request $request,Listing $listing){
    //  dd($request->file('logo'));

    // makesure loged in user is owner 
    if($listing->user_id != auth()->id()){
        abort(403,'unauthorized action');
    }

    $formFields = $request->validate([
        'title' => 'required',
        'company' => 'required',
        'location' => 'required',
        'website' => 'required',
        'tags' => 'required',
        'email' => ['required','email'], 
        'description'  =>'required',
    ]);
    // the formfield option 
    //  or use unguard in the app/providers/appserviceprovider/ at boot place Model::unguard(); and import the model class
    if($request->hasFile('logo')) {
        $formFields['logo'] = $request->file('logo')->store('logos', 'public');
    }

    // after this run php artisan storage:link

    $listing->update($formFields);

    return back()->with('message', 'listing updated successfuly!');
}

// delete listing
public function destroy(Listing $listing){
     // makesure loged in user is owner 
     if($listing->user_id != auth()->id()){
        abort(403,'unauthorized action');
    }

    $listing->delete();
    return redirect('/')->with('message', 'Listing deleted successfully');
}

// manage listings

public function manage(){
    return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
}

}


