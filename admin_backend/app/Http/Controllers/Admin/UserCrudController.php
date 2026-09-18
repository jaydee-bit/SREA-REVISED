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
        // Staff account management is municipality-wide, not scoped to
        // one barangay — unlike Incidents/Analytics/etc., there's no
        // sensible "your own slice" of this to show a barangay admin.
        // Blocking it here (not just hiding the sidebar link) means
        // typing /admin/user directly doesn't bypass anything either —
        // every operation (list, create, update, delete, show) routes
        // through this setup() first.
        if (!backpack_user()->isSuperAdmin()) {
            abort(403, 'Unauthorized. Staff account management is municipality-wide and restricted to super admins.');
        }

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
        CRUD::setFromDb(); // set fields from db columns.

        // These belong to a resident's profile, not a staff account — a
        // staff member (Admin/Responder) is created by another admin here,
        // not self-registered, so there's no ID-verification step to
        // capture for them. Names guessed from your DB columns; if any of
        // these don't match your actual column names, let me know and
        // I'll correct them.
        CRUD::removeFields([
            'birth_date',
            'profile_image',
            'street',
            'province',
            'municipality',
            'valid_id_type',
            'valid_id_photo',
        ]);

        // Lay every remaining field out two-per-row instead of the default
        // full-width stack. File/image/textarea fields are left alone since
        // squeezing those into half-width usually looks worse, not better.
        foreach (CRUD::fields() as $field) {
            if (in_array($field['type'], ['textarea', 'wysiwyg', 'upload', 'upload_multiple', 'image'])) {
                continue;
            }

            CRUD::modifyField($field['name'], [
                'wrapper' => array_merge($field['wrapper'] ?? [], ['class' => 'form-group col-md-6']),
            ]);
        }

        CRUD::modifyField('role', [
            'type' => 'select_from_array',
            'options' => ['admin' => 'Admin', 'responder' => 'Responder'],
            'allows_null' => false,
            'attributes' => ['id' => 'field_role'],
            'wrapper' => ['class' => 'form-group col-md-6'],
        ]);

        // Same reasoning as the role field above — a barangay admin's
        // entire scoping (Incidents, Response Monitor, Live Map, Analytics,
        // Audit Trail, assistance requests) depends on this string exactly
        // matching a real Barangay row. A free-text field risks a typo or
        // stray space silently breaking that admin's access with no error
        // anywhere pointing to why.
        CRUD::modifyField('barangay', [
            'type' => 'select_from_array',
            'options' => \App\Models\Barangay::orderBy('name')->pluck('name', 'name')->toArray(),
            'allows_null' => true,
            'hint' => 'Required for a barangay-scoped Admin, or a Responder assigned to a specific barangay. Leave blank for a Super Admin or a municipality-wide responder.',
            'wrapper' => ['id' => 'wrapper_barangay', 'class' => 'form-group col-md-6'],
        ]);

        CRUD::modifyField('is_super_admin', [
            'label' => 'Super Admin',
            'type' => 'checkbox',
            'hint' => 'Grants access to every barangay\'s data and Staff Accounts. Leave unchecked for a barangay-scoped Admin.',
            'attributes' => ['id' => 'field_is_super_admin'],
            'wrapper' => ['id' => 'wrapper_is_super_admin', 'class' => 'form-group col-md-6'],
        ]);

        // Gender only matters for a Responder — it's used on the Live Map
        // / dispatch side, not for an Admin account, so it's hidden by
        // the same Role toggle as Team/Vehicle below rather than always
        // showing regardless of role.
        CRUD::modifyField('gender', [
            'wrapper' => ['id' => 'wrapper_gender', 'class' => 'form-group col-md-6'],
        ]);

        // These two don't live on the `users` table — they belong to
        // responder_profiles. They're virtual form fields only; store()
        // and update() below pull them out of the request before Backpack
        // tries to save them onto the User model, and write them to the
        // ResponderProfile instead.
        CRUD::addField([
            'name' => 'responder_team',
            'label' => 'Team',
            'type' => 'text',
            'hint' => 'Only used when Role is Responder.',
            'wrapper' => ['id' => 'wrapper_responder_team', 'class' => 'form-group col-md-6'],
        ]);

        CRUD::addField([
            'name' => 'responder_vehicle',
            'label' => 'Vehicle',
            'type' => 'text',
            'hint' => 'Only used when Role is Responder.',
            'wrapper' => ['id' => 'wrapper_responder_vehicle', 'class' => 'form-group col-md-6'],
        ]);

        // Show/hide Team, Vehicle, Super Admin, and Barangay based on the
        // selected Role (and, once Role = Admin, whether Super Admin is
        // checked) so the form only ever shows fields that are actually
        // relevant to the account being created. Re-runs on every change
        // of either control, and once on load so an edit form reflects the
        // entry's existing values immediately.
        CRUD::addField([
            'name' => 'staff_form_toggle_script',
            'type' => 'custom_html',
            'value' => '
                <script>
                    function sreaToggleStaffFormFields() {
                        var roleField = document.getElementById("field_role");
                        var superAdminField = document.getElementById("field_is_super_admin");
                        if (!roleField) return;

                        var isResponder = roleField.value === "responder";
                        var isSuperAdmin = superAdminField ? superAdminField.checked : false;

                        var teamWrapper = document.getElementById("wrapper_responder_team");
                        var vehicleWrapper = document.getElementById("wrapper_responder_vehicle");
                        var genderWrapper = document.getElementById("wrapper_gender");
                        var superAdminWrapper = document.getElementById("wrapper_is_super_admin");
                        var barangayWrapper = document.getElementById("wrapper_barangay");

                        if (teamWrapper) teamWrapper.style.display = isResponder ? "" : "none";
                        if (vehicleWrapper) vehicleWrapper.style.display = isResponder ? "" : "none";
                        if (genderWrapper) genderWrapper.style.display = isResponder ? "" : "none";

                        // Only an Admin can be a Super Admin — a Responder never is.
                        if (superAdminWrapper) superAdminWrapper.style.display = isResponder ? "none" : "";
                        if (isResponder && superAdminField) superAdminField.checked = false;

                        // Barangay is meaningless once Super Admin is checked.
                        if (barangayWrapper) barangayWrapper.style.display = (isSuperAdmin && !isResponder) ? "none" : "";
                    }

                    document.addEventListener("DOMContentLoaded", function () {
                        sreaToggleStaffFormFields();
                        var roleField = document.getElementById("field_role");
                        var superAdminField = document.getElementById("field_is_super_admin");
                        if (roleField) roleField.addEventListener("change", sreaToggleStaffFormFields);
                        if (superAdminField) superAdminField.addEventListener("change", sreaToggleStaffFormFields);
                    });
                </script>
            ',
        ]);

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
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