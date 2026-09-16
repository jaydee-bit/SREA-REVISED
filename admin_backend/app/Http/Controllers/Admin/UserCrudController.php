<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Backpack\CRUD\app\Library\Widget;

/**
 * Class UserCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class UserCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as traitStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as traitUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\User::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/user');
        CRUD::setEntityNameStrings('staff account', 'staff accounts');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.

        // Simple role filter using a query string (no Backpack Pro needed)
        if (request()->has('role') && in_array(request('role'), ['admin', 'responder'])) {
            CRUD::addClause('where', 'role', request('role'));
        }

        Widget::add()->type('view')->view('vendor.backpack.ui.crud.user_role_filter');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
{
    CRUD::setValidation(UserRequest::class);

    CRUD::field('name')
        ->type('text')
        ->wrapper(['class' => 'form-group col-md-6']);

    CRUD::field('email')
        ->type('email')
        ->wrapper(['class' => 'form-group col-md-6']);

    CRUD::field('password')
        ->type('password')
        ->hint('Leave blank to keep the current password (when editing).')
        ->wrapper(['class' => 'form-group col-md-6']);

    CRUD::field('phone')
        ->type('text')
        ->wrapper(['class' => 'form-group col-md-6']);

    CRUD::field('role')
        ->type('select_from_array')
        ->options(['admin' => 'Admin', 'responder' => 'Responder'])
        ->allows_null(false)
        ->wrapper(['class' => 'form-group col-md-6']);

    CRUD::field('barangay')
        ->type('text')
        ->hint('The barangay this staff member is primarily assigned to.')
        ->wrapper(['class' => 'form-group col-md-6']);

    CRUD::field('responder_team')
        ->type('text')
        ->label('Team')
        ->hint('Only used when Role is Responder.')
        ->wrapper(['class' => 'form-group col-md-6']);

    CRUD::field('responder_vehicle')
        ->type('text')
        ->label('Vehicle')
        ->hint('Only used when Role is Responder.')
        ->wrapper(['class' => 'form-group col-md-6']);
}

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Pulls the responder-only fields off the request before the User
     * model gets saved (they aren't columns on `users`), then syncs a
     * ResponderProfile row so creating a responder here is a single step
     * instead of needing a second manual profile setup elsewhere.
     */
    private function syncResponderProfile(): array
    {
        $team = $this->crud->getRequest()->input('responder_team');
        $vehicle = $this->crud->getRequest()->input('responder_vehicle');

        $this->crud->getRequest()->request->remove('responder_team');
        $this->crud->getRequest()->request->remove('responder_vehicle');

        return ['team' => $team, 'vehicle' => $vehicle];
    }

    private function applyResponderProfile($entry, array $profileData): void
    {
        if (!$entry || $entry->role !== 'responder') {
            return;
        }

        $existing = \App\Models\ResponderProfile::where('user_id', $entry->id)->first();

        \App\Models\ResponderProfile::updateOrCreate(
            ['user_id' => $entry->id],
            [
                'team' => $profileData['team'],
                'vehicle' => $profileData['vehicle'],
                // Only default to Standby on first creation — don't
                // overwrite an existing responder's live status just
                // because an admin edited their Team/Vehicle on Update.
                'current_status' => $existing->current_status ?? 'Standby',
            ]
        );
    }

    public function store()
    {
        $profileData = $this->syncResponderProfile();
        $response = $this->traitStore();
        $this->applyResponderProfile($this->crud->entry, $profileData);

        return $response;
    }

    public function update()
    {
        $profileData = $this->syncResponderProfile();
        $response = $this->traitUpdate();
        $this->applyResponderProfile($this->crud->entry, $profileData);

        return $response;
    }
}