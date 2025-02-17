@extends('layouts.app')
@section('page')
requests
@endsection
@section('title')
Requests
@endsection
@section('content')
        <main class="h-full pb-16 overflow-y-auto">
          <div class="container grid px-6 mx-auto">
            <h2
              class="my-6 text-2xl font-semibold text-gray-700 dark:text-gray-200"
            >
              Requests
            </h2>
           
            <div class="w-full overflow-hidden rounded-lg shadow-xs">
              <div class="w-full overflow-x-auto">
                <table class="w-full whitespace-no-wrap" id="images">
                  <thead>
                    <tr
                      class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b dark:border-gray-700 bg-gray-50 dark:text-gray-400 dark:bg-gray-800"
                    >
                      <th class="px-4 py-3">Registrant</th>
                      <th class="px-4 py-3">Email</th>
                      <th class="px-4 py-3">Phone</th>
                      <th class="px-4 py-3">Receipt</th>
                      <th class="px-4 py-3">Ticket Type</th>
                      <th class="px-4 py-3">Status</th>
                      <th class="px-4 py-3">Date</th>
                      <th class="px-4 py-3">Actions</th>
                    </tr>
                  </thead>
                  <tbody
                    class="bg-white divide-y dark:divide-gray-700 dark:bg-gray-800"
                  >
                    @foreach ($requests as $request)
                        
                    <tr class="text-gray-700 dark:text-gray-400">
                      <td class="px-4 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $request->name }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $request->email }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-3">
                        <div class="flex items-center text-sm">
                          <div>
                            <p class="font-semibold">{{ $request->phone_number }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3 text-sm">
                        <div class="w-12 h-12 relative">
                            
                            <div class=" bg-black absolute w-full h-full top-0 left-0 flex justify-center items-center">
                                <i class="las la-search-plus text-xl"></i>
                            </div>      
                            <img src="{{ asset('images/'.$request->picture) }}" class="transition-all absolute top-0 left-0 flex justify-center items-center opacity-100 hover:opacity-40 w-full h-full object-cover" alt=""> 
                        </div>                   
                      </td>
                      <td class="px-4 py-3 text-sm">
                        {{ $request->ticket_type->name??"" }} - {{ $request->ticket_type->price??"" }}EGP
                      </td>
                      <td class="px-4 py-3 text-xs">
                        @if($request->status === null)
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-yellow-600 bg-yellow-100 rounded-full dark:bg-yellow-600 dark:text-yellow-100"
                        >
                          Pending
                        </span>
                        @elseif($request->status == 1)
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-green-600 bg-green-100 rounded-full dark:bg-green-600 dark:text-green-100"
                        >
                          Approved
                        </span>
                        @else
                        <span
                          class="px-2 py-1 font-semibold leading-tight text-red-600 bg-red-100 rounded-full dark:bg-red-600 dark:text-red-100"
                        >
                          Declined
                        </span>
                        @endif
                      </td>
                      <td class="px-4 py-3 text-sm">
                        {{ date('Y/m/d h:i A',strtotime($request->created_at)) }}
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-center space-x-4 text-sm">
                            <button
                            @if ($request->status !== null)
                                disabled
                            @else
                            onclick="display_popup(this)"
                            data-title="Are you sure you want to ACCEPT {{ explode(' ',$request->name)[0] }}'s request?"
                            data-content="By continuing, you ensure that this request is completely accepted and cannot be undone. An email will be sent to them confirming their request and providing a QR Code image to be able to enter the event!"
                            data-action="{{ route('admin.accept',$request->id) }}"
                            @endif
                            class="flex items-center group disabled:hover:bg-inherit disabled:cursor-not-allowed  hover:bg-gray-300 dark:hover:bg-gray-600 justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                            aria-label="Accept"
                          >
                            <i class="las la-check text-xl group-disabled:text-gray-500 text-green-500"></i>
                          </button>
                          <button
                            @if ($request->status !== null)
                                disabled
                            @else
                            onclick="display_popup(this)"
                            data-title="Are you sure you want to REJECT {{ explode(' ',$request->name)[0] }}'s request?"
                            data-content="By continuing, you ensure that this request is completely rejected and cannot be undone. An email will be sent to them informing them with the status of their request!"
                            data-action="{{ route('admin.reject',$request->id) }}"
                            @endif
                            class="flex items-center group disabled:hover:bg-inherit disabled:cursor-not-allowed hover:bg-gray-300 dark:hover:bg-gray-600 justify-between px-2 py-2 text-sm font-medium leading-5 text-purple-600 rounded-lg dark:text-gray-400 focus:outline-none focus:shadow-outline-gray"
                            aria-label="Reject"
                          >
                            <i class="las la-times text-xl group-disabled:text-gray-500 text-red-500"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                    @endforeach

                  </tbody>
                </table>
              </div>
            <div class="mt-4">
             {{$requests->links('pagination::tailwind')}}
            </div>
          </div>
        </main>

@endsection