<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemSpecController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\IncomingInvoiceController;
use App\Http\Controllers\OutgoingInvoiceController;

// Route::get('/', function () {
//     return view('pages.welcome');
// });

Route::get('/', function () {
    return view('pages.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/user-status', function () {
    // Check if the user is authenticated
    if (auth()->check()) {
        return response()->json([
            'isAuthenticated' => true,
            'userName' => auth()->user()->name, // Or any data you want to display
            'dashboardUrl' => route('dashboard'), // Use the named route
        ]);
    }

    return response()->json([
        'isAuthenticated' => false,
        'loginUrl' => route('login'), // Use the named route
    ]);
})->name('user.status');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('users', UserController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('levels', LevelController::class);
    Route::resource('clients', ClientController::class);
    Route::resource('vendors', VendorController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('taxes', TaxController::class);

    Route::resource('items', ItemController::class);
    Route::post('/quick-create/item', [ItemController::class, 'quickStore'])->name('items.quickStore');

    Route::resource('item-specs', ItemSpecController::class);
    Route::post('/quick-create/item-spec', [ItemSpecController::class, 'quickStore'])->name('item-specs.quickStore');

    Route::post('orders/bulk-update', [OrderController::class, 'massUpdate'])->name('orders.mass-update');
    // Route::put('orders/mass-update', [OrderController::class, 'massUpdate'])->name('orders.mass-update');
    Route::get('orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::resource('orders', OrderController::class);

    Route::put('outgoing-invoices/mass-update', [OutgoingInvoiceController::class, 'massUpdate'])->name('outgoing-invoices.mass-update');
    Route::get('outgoing-invoices/export', [OutgoingInvoiceController::class, 'export'])->name('outgoing-invoices.export');
    Route::resource('outgoing-invoices', OutgoingInvoiceController::class);
    Route::get('/outgoing-invoices/{outgoingInvoice}/document', [OutgoingInvoiceController::class, 'generateSingleDocument'])->name('outgoing-invoices.generate-single');
    Route::post('/outgoing-invoices/documents/mass-generate', [OutgoingInvoiceController::class, 'generateMassDocuments'])->name('outgoing-invoices.generate-documents');

    Route::put('incoming-invoices/mass-update', [IncomingInvoiceController::class, 'massUpdate'])->name('incoming-invoices.mass-update');
    Route::get('incoming-invoices/export', [IncomingInvoiceController::class, 'export'])->name('incoming-invoices.export');
    Route::resource('incoming-invoices', IncomingInvoiceController::class);
});

require __DIR__.'/auth.php';
