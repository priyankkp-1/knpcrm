<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\MailtemplatesController;
use App\Http\Controllers\ActivityLogsController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\CustomersGroupsController;
use App\Http\Controllers\CurrenciesController;
use App\Http\Controllers\PaymentModeController;
use App\Http\Controllers\ExpencesCategoriesController;
use App\Http\Controllers\ContractTypesController;
use App\Http\Controllers\TaxesController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\CustomFieldsController;
use App\Http\Controllers\CustomFieldValueController;
use App\Http\Controllers\WebToLeadController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\TaskCheckListController;
use App\Http\Controllers\TaskCommentsController;
use App\Http\Controllers\TaskDocumentsController;
use App\Http\Controllers\WebToLeadFormSubmitController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\WebToLeadDesignController;
use App\Http\Controllers\NotesController;
use App\Http\Controllers\RemindersController;
use App\Http\Controllers\DocumentsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login',[LoginController::class, 'adminLogin'])->name('adminLogin');
Route::post('web_to_lead_form_submit',[WebToLeadFormSubmitController::class, 'create'])->name('web_to_lead_submit');
Route::get('lead_thank_you',[WebToLeadFormSubmitController::class, 'thank_you'])->name('lead_thank_you');
Route::get('template_html/{form_id}',[WebToLeadDesignController::class, 'template_html']);

Route::group( ['prefix' => '','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   // authenticated staff routes here 
    Route::get('logout',[LoginController::class, 'adminLogout']);
});

Route::group( ['prefix' => 'modules','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
    // authenticated staff routes here 
    Route::get('getlist',[PermissionController::class, 'getlist']);
 });

 Route::group( ['prefix' => 'role','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
    Route::post('syncrolepermission',[PermissionController::class, 'syncPermissionRole'])->name('syncrolepermission');
    Route::post('create',[PermissionController::class, 'createRoleWithPermission'])->name('createrolewithpermission');
    Route::post('delete',[PermissionController::class, 'deleteRole'])->name('delete-role');
    Route::get('getByRoleid/{id}',[PermissionController::class, 'getByRoleid']);
    Route::get('getrolelist',[PermissionController::class, 'getRolelist']);
 });


 Route::group( ['prefix' => 'source','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[SourceController::class, 'update'])->name('update-source');
   Route::post('create',[SourceController::class, 'create'])->name('create-source');
   Route::post('delete',[SourceController::class, 'delete'])->name('delete-source');
   Route::get('getByid/{id}',[SourceController::class, 'getByid']);
   Route::get('getlist',[SourceController::class, 'getlist']);
});

Route::group( ['prefix' => 'customers-groups','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[CustomersGroupsController::class, 'update'])->name('update-customers-groups');
   Route::post('create',[CustomersGroupsController::class, 'create'])->name('create-customers-groups');
   Route::post('delete',[CustomersGroupsController::class, 'delete'])->name('delete-customers-groups');
   Route::get('getByid/{id}',[CustomersGroupsController::class, 'getByid']);
   Route::get('getlist',[CustomersGroupsController::class, 'getlist']);
});

Route::group( ['prefix' => 'paymentmodes','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[PaymentModeController::class, 'update'])->name('update-paymentmodes');
   Route::post('create',[PaymentModeController::class, 'create'])->name('create-paymentmodes');
   Route::post('delete',[PaymentModeController::class, 'delete'])->name('delete-paymentmodes');
   Route::get('getByid/{id}',[PaymentModeController::class, 'getByid']);
   Route::get('getlist',[PaymentModeController::class, 'getlist']);
   Route::get('getRecords',[PaymentModeController::class, 'getRecords']);
});

Route::group( ['prefix' => 'expences-categories','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[ExpencesCategoriesController::class, 'update'])->name('update-expences-categories');
   Route::post('create',[ExpencesCategoriesController::class, 'create'])->name('create-expences-categories');
   Route::post('delete',[ExpencesCategoriesController::class, 'delete'])->name('delete-expences-categories');
   Route::get('getByid/{id}',[ExpencesCategoriesController::class, 'getByid']);
   Route::get('getlist',[ExpencesCategoriesController::class, 'getlist']);
   Route::get('getRecords',[ExpencesCategoriesController::class, 'getRecords']);
});

Route::group( ['prefix' => 'contract-types','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[ContractTypesController::class, 'update'])->name('update-contract-types');
   Route::post('create',[ContractTypesController::class, 'create'])->name('create-contract-types');
   Route::post('delete',[ContractTypesController::class, 'delete'])->name('delete-contract-types');
   Route::get('getByid/{id}',[ContractTypesController::class, 'getByid']);
   Route::get('getlist',[ContractTypesController::class, 'getlist']);
   Route::get('getRecords',[ContractTypesController::class, 'getRecords']);
});

Route::group( ['prefix' => 'currency','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('exchangerate',[CurrenciesController::class, 'exchangeRate'])->name('exchangeRate-currency');
   Route::post('exchangerateList',[CurrenciesController::class, 'exchangerateList'])->name('exchangerateList-currency');

   Route::post('update',[CurrenciesController::class, 'update'])->name('update-currency');
   Route::post('create',[CurrenciesController::class, 'create'])->name('create-currency');
   Route::post('delete',[CurrenciesController::class, 'delete'])->name('delete-currency');
   Route::get('getRecords',[CurrenciesController::class, 'getRecords']);
   Route::get('getByid/{id}',[CurrenciesController::class, 'getByid']);
   Route::get('getlist',[CurrenciesController::class, 'getlist']);
});


 
 Route::group( ['prefix' => 'staff','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('create',[AdminController::class, 'create'])->name('create-staff');
   Route::get('getlist',[AdminController::class, 'getList'])->name('admin-list');
   Route::get('getById/{id}',[AdminController::class, 'getById']);
   Route::post('delete',[AdminController::class, 'delete'])->name('delete-staff');
   Route::post('edit',[AdminController::class, 'edit'])->name('edit-staff');
});

Route::group( ['prefix' => 'settings','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::get('getlist',[CompanySettingsController::class, 'getList'])->name('settings-list');
   Route::post('edit',[CompanySettingsController::class, 'edit'])->name('edit-settings');
});

Route::group( ['prefix' => 'activity-logs','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::get('getlist',[ActivityLogsController::class, 'getList'])->name('activitylogs-list');
});

Route::group( ['prefix' => 'mailtemplate','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::get('getlist',[MailtemplatesController::class, 'getList'])->name('mailtemplate-list');
   Route::get('getById/{id}',[MailtemplatesController::class, 'getById']);
   Route::post('edit',[MailtemplatesController::class, 'edit'])->name('edit-mailtemplate');
   Route::post('enabledisable',[MailtemplatesController::class, 'enabledisable'])->name('enabledisable-mailtemplate');
});


Route::group( ['prefix' => 'taxes','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[TaxesController::class, 'update'])->name('update-taxes');
   Route::post('create',[TaxesController::class, 'create'])->name('create-taxes');
   Route::post('delete',[TaxesController::class, 'delete'])->name('delete-taxes');
   Route::get('getByid/{id}',[TaxesController::class, 'getByid']);
   Route::get('getlist',[TaxesController::class, 'getlist']);
   Route::get('getRecords',[TaxesController::class, 'getRecords']);
});


Route::group( ['prefix' => 'status','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[StatusController::class, 'update'])->name('update-status');
   Route::post('create',[StatusController::class, 'create'])->name('create-status');
   Route::post('delete',[StatusController::class, 'delete'])->name('delete-status');
   Route::get('getByid/{id}',[StatusController::class, 'getByid']);
   Route::get('getlist',[StatusController::class, 'getlist']);
   Route::get('getRecords',[StatusController::class, 'getRecords']);
});


Route::group( ['prefix' => 'announcement','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[AnnouncementController::class, 'update'])->name('update-announcement');
   Route::post('create',[AnnouncementController::class, 'create'])->name('create-announcement');
   Route::post('delete',[AnnouncementController::class, 'delete'])->name('delete-announcement');
   Route::get('getByid/{id}',[AnnouncementController::class, 'getByid']);
   Route::get('getlist',[AnnouncementController::class, 'getlist']);
   Route::get('getRecords',[AnnouncementController::class, 'getRecords']);
   Route::get('staffdissmissed/{id}',[AnnouncementController::class, 'staffdissmissed']);
});


Route::group( ['prefix' => 'item-group','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[ItemGroupController::class, 'update'])->name('update-itemgroup');
   Route::post('create',[ItemGroupController::class, 'create'])->name('create-itemgroup');
   Route::post('delete',[ItemGroupController::class, 'delete'])->name('delete-itemgroup');
   Route::get('getByid/{id}',[ItemGroupController::class, 'getByid']);
   Route::get('getlist',[ItemGroupController::class, 'getlist']);
   Route::get('getRecords',[ItemGroupController::class, 'getRecords']);
});


Route::group( ['prefix' => 'products','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[ProductsController::class, 'update'])->name('update-products');
   Route::post('create',[ProductsController::class, 'create'])->name('create-products');
   Route::post('delete',[ProductsController::class, 'delete'])->name('delete-products');
   Route::get('getByid/{id}',[ProductsController::class, 'getByid']);
   Route::get('getlist',[ProductsController::class, 'getlist']);
   Route::get('getRecords',[ProductsController::class, 'getRecords']);
});

Route::group( ['prefix' => 'countries','middleware' => [] ],function(){
   Route::get('getlist',[AdminController::class, 'getCountry'])->name('country-list');
});

Route::group( ['prefix' => 'custom-fields','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[CustomFieldsController::class, 'update'])->name('update-customfields');
   Route::post('create',[CustomFieldsController::class, 'create'])->name('create-customfields');
   Route::post('delete',[CustomFieldsController::class, 'delete'])->name('delete-customfields');
   Route::get('getByid/{id}',[CustomFieldsController::class, 'getByid']);
   Route::get('getlist',[CustomFieldsController::class, 'getlist']);
   Route::get('getRecords',[CustomFieldsController::class, 'getRecords']);
});

Route::group( ['prefix' => 'custom-field-value','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('create',[CustomFieldValueController::class, 'create'])->name('create-customfieldvalue');
   Route::post('getRecords',[CustomFieldValueController::class, 'getRecords']);
});

Route::group( ['prefix' => 'web-to-lead','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[WebToLeadController::class, 'update'])->name('update-webtolead');
   Route::post('create',[WebToLeadController::class, 'create'])->name('create-webtolead');
   Route::post('delete',[WebToLeadController::class, 'delete'])->name('delete-webtolead');
   Route::get('getByid/{id}',[WebToLeadController::class, 'getByid']);
   Route::get('getlist',[WebToLeadController::class, 'getlist']);
   
});

Route::group( ['prefix' => 'leads','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update-status',[LeadController::class, 'lead_status_update'])->name('update-status');
   Route::post('update-assignee',[LeadController::class, 'lead_assignee_update'])->name('update-assignee');
   Route::post('update',[LeadController::class, 'update'])->name('update-leads');
   Route::post('create',[LeadController::class, 'create'])->name('create-leads');
   Route::post('delete',[LeadController::class, 'delete'])->name('delete-leads');
   Route::get('getByid/{id}',[LeadController::class, 'getByid']);
   Route::get('getlist',[LeadController::class, 'getlist']);
   Route::get('system_build_field',[LeadController::class, 'systemInBuildField']);
   Route::get('admin_system_custom_field/{field_to}',[LeadController::class, 'admin_system_custom_field']);
   Route::post('lead_convert_to_customer',[LeadController::class, 'lead_convert_to_customer']);
   Route::post('/import',[LeadController::class,'import'])->name('import');

});

Route::group( ['prefix' => 'tasks','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[TasksController::class, 'update'])->name('update-tasks');
   Route::post('create',[TasksController::class, 'create'])->name('create-tasks');
   Route::post('delete',[TasksController::class, 'delete'])->name('delete-tasks');
   Route::get('getByid/{id}',[TasksController::class, 'getByid']);
   Route::get('getlist',[TasksController::class, 'getlist']);
   Route::get('getRecords',[TasksController::class, 'getRecords']);
});

Route::group( ['prefix' => 'task-checklist','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[TaskCheckListController::class, 'update'])->name('update-task-checklist');
   Route::post('create',[TaskCheckListController::class, 'create'])->name('create-task-checklist');
   Route::post('delete',[TaskCheckListController::class, 'delete'])->name('delete-task-checklist');
   Route::get('getByid/{id}',[TaskCheckListController::class, 'getByid']);
   Route::get('getlist',[TaskCheckListController::class, 'getlist']);
});

Route::group( ['prefix' => 'task-comments','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[TaskCommentsController::class, 'update'])->name('update-task-comments');
   Route::post('create',[TaskCommentsController::class, 'create'])->name('create-task-comments');
   Route::post('delete',[TaskCommentsController::class, 'delete'])->name('delete-task-comments');
   Route::get('getByid/{id}',[TaskCommentsController::class, 'getByid']);
   Route::get('getlist',[TaskCommentsController::class, 'getlist']);
});

Route::group( ['prefix' => 'task-documents','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[TaskDocumentsController::class, 'update'])->name('update-task-documents');
   Route::post('create',[TaskDocumentsController::class, 'create'])->name('create-task-documents');
   Route::post('delete',[TaskDocumentsController::class, 'delete'])->name('delete-task-documents');
   Route::get('getByid/{id}',[TaskDocumentsController::class, 'getByid']);
   Route::get('getlist',[TaskDocumentsController::class, 'getlist']);
});

Route::group( ['prefix' => 'customers','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[CustomersController::class, 'update'])->name('update-task-documents');
   Route::post('create',[CustomersController::class, 'create'])->name('create-task-documents');
   Route::post('delete',[CustomersController::class, 'delete'])->name('delete-task-documents');
   Route::get('getByid/{id}',[CustomersController::class, 'getByid']);
   Route::get('getlist',[CustomersController::class, 'getlist']);
  
});

Route::group( ['prefix' => 'templates','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::get('getlist',[TemplateController::class, 'getList'])->name('list-templates');
   Route::post('edit',[TemplateController::class, 'update'])->name('edit-templates');
});


Route::group( ['prefix' => 'notes','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[NotesController::class, 'update'])->name('update-notes');
   Route::post('create',[NotesController::class, 'create'])->name('create-notes');
   Route::post('delete',[NotesController::class, 'delete'])->name('delete-notes');
   Route::get('getByid/{id}',[NotesController::class, 'getByid']);
   Route::get('getlist',[NotesController::class, 'getlist']);
   Route::get('getRecords',[NotesController::class, 'getRecords']);
});


Route::group( ['prefix' => 'reminders','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[RemindersController::class, 'update'])->name('update-reminders');
   Route::post('create',[RemindersController::class, 'create'])->name('create-reminders');
   Route::post('delete',[RemindersController::class, 'delete'])->name('delete-reminders');
   Route::get('getByid/{id}',[RemindersController::class, 'getByid']);
   Route::get('getlist',[RemindersController::class, 'getlist']);
   Route::get('getRecords',[RemindersController::class, 'getRecords']);
});


Route::group( ['prefix' => 'lead-documents','middleware' => ['auth:admin-api','scopes:admin'] ],function(){
   Route::post('update',[DocumentsController::class, 'update'])->name('update-lead-documents');
   Route::post('create',[DocumentsController::class, 'create'])->name('create-lead-documents');
   Route::post('delete',[DocumentsController::class, 'delete'])->name('delete-lead-documents');
   Route::get('getByid/{id}',[DocumentsController::class, 'getByid']);
   Route::get('getlist',[DocumentsController::class, 'getlist']);
});


?>