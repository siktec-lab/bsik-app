<?php

use \Siktec\Bsik\Module\Modules;
use \Siktec\Bsik\Module\Module;
use \Siktec\Bsik\Module\ModuleView;
use \Siktec\Bsik\Privileges as Priv;
use \Siktec\Bsik\Objects\SettingsObject;

/****************************************************************************/
/*******************  local Includes    *************************************/
/****************************************************************************/
//require_once "includes".DS."components.php";


/****************************************************************************/
/*******************  required privileges for module / views    *************/
/****************************************************************************/
$module_policy = new Priv\RequiredPrivileges();
$module_policy->define(
    new Priv\Default\PrivAccess(manage : true)
);

/****************************************************************************/
/*******************  Register Module  **************************************/
/****************************************************************************/

Modules::register_module_once(new Module(
    name          : "dev",
    privileges    : $module_policy,
    views         : ["forms","colors"],
    default_view  : "forms"
)); 

/****************************************************************************/
/*******************  View - forms  *****************************************/
/****************************************************************************/

Modules::module("dev")->register_view(
    view : new ModuleView(
        name        : "forms",
        privileges  : null,
        settings    : new SettingsObject([
            "title"         => "Bootstrap 5 Forms",
            "description"   => "SikForms based on bootstrap 5 forms",
        ])
    ),
    render      : function() {

        /** @var Module $this */

        //Return forms example:
        return <<<HTML
            <div class='container mt-3'>
                <div class='row justify-content-center'>
                    <div class='col-8'>
                        <h2 class='module-title'>Bootstrap-5 SikForms Example</h2>
                    </div>
                </div>
                <div class='row justify-content-center'>
                    <div class='col-8 sik-form-init'>
                        <div class='col-12 npm-search-results sik-form-init'>
                            <form class="">
                                <div class="mb-3">
                                    <label for="exampleInputEmail1" class="form-label">Email address</label>
                                    <input type="email" class="form-control input-carret" id="exampleInputEmail1" aria-describedby="emailHelp">
                                    <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="exampleInputPassword1" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1">
                                </div>
                                <div class="mb-3 ">
                                    <label for="gennn1 " class="form-label">Validation 1</label>
                                    <input type="text " class="form-control input-carret is-invalid" id="gennn1" aria-describedby="emailHelp">
                                    <div class="invalid-feedback">
                                        Please choose a username.
                                    </div>
                                </div>
                                <div class="mb-3 is-valid">
                                    <label for="gennn2" class="form-label">Validation 2</label>
                                    <input type="text" class="form-control input-carret is-valid" id="gennn2" aria-describedby="emailHelp">
                                    <div class="valid-feedback">
                                        Looks good!
                                    </div>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1">Check me out</label>
                                </div>
                                <div class="mb-3">
                                    <label for="exampleFormControlTextarea1" class="form-label">Example textarea</label>
                                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                                </div>
                                <div class="mb-3">
                                    <input class="form-control form-control-lg input-carret" type="text" placeholder=".form-control-lg" aria-label=".form-control-lg">
                                    <input class="form-control input-carret" type="text" placeholder="Default input" aria-label="default input">
                                    <input class="form-control form-control-sm input-carret" type="text" placeholder=".form-control-sm" aria-label=".form-control-sm">
                                </div>
                                <div class="mb-3">
                                    <input class="form-control" type="text" placeholder="Readonly input here..." aria-label="readonly input example" readonly>
                                    <input class="form-control" type="text" placeholder="Disabled input" aria-label="Disabled input example" disabled>
                                    <input class="form-control" type="text" placeholder="Disabled readonly input" aria-label="Disabled input example" disabled readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="formFile" class="form-label">Default file input example</label>
                                    <input class="form-control" type="file" id="formFile">
                                </div>
                                <div class="mb-3">
                                    <label for="formFileMultiple" class="form-label">Multiple files input example</label>
                                    <input class="form-control" type="file" id="formFileMultiple" multiple>
                                </div>
                                <div class="mb-3">
                                    <label for="formFileDisabled" class="form-label">Disabled file input example</label>
                                    <input class="form-control" type="file" id="formFileDisabled" disabled>
                                </div>
                                <div class="mb-3">
                                    <label for="formFileSm" class="form-label">Small file input example</label>
                                    <input class="form-control form-control-sm" id="formFileSm" type="file">
                                </div>
                                <div class="mb-3">
                                    <label for="formFileLg" class="form-label">Large file input example</label>
                                    <input class="form-control form-control-lg" id="formFileLg" type="file">
                                </div>
                                <div class="mb-3">
                                    <label for="exampleColorInput" class="form-label">Color picker</label>
                                    <input type="color" class="form-control form-control-color" id="exampleColorInput" value="#563d7c" title="Choose your color">
                                </div>
                                <div class="mb-3">
                                    <label for="exampleDataList" class="form-label">Datalist example</label>
                                    <input class="form-control" list="datalistOptions" id="exampleDataList" placeholder="Type to search...">
                                    <datalist id="datalistOptions">
                                        <option value="San Francisco">
                                        <option value="New York">
                                        <option value="Seattle">
                                        <option value="Los Angeles">
                                        <option value="Chicago">
                                    </datalist>
                                </div>
                                <div class="mb-3">
                                    <select class="form-select" aria-label="Default select example">
                                        <option selected>Open this select menu</option>
                                        <option value="1">One</option>
                                        <option value="2">Two</option>
                                        <option value="3">Three</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <select class="form-select" aria-label="Default select example" disabled>
                                        <option selected>Open this select menu</option>
                                        <option value="1">One</option>
                                        <option value="2">Two</option>
                                        <option value="3">Three</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <select class="form-select form-select-sm" aria-label="Default select example">
                                        <option selected>Open this select menu</option>
                                        <option value="1">One</option>
                                        <option value="2">Two</option>
                                        <option value="3">Three</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <select class="form-select form-select-lg" aria-label="Default select example">
                                        <option selected>Open this select menu</option>
                                        <option value="1">One</option>
                                        <option value="2">Two</option>
                                        <option value="3">Three</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary me-2">Primary</button>
                                    <button type="button" class="btn btn-secondary me-2">Secondary</button>
                                    <button type="button" class="btn btn-info me-2">Info</button>
                                    <button type="button" class="btn btn-success me-2">Success</button>
                                    <button type="button" class="btn btn-danger me-2">Danger</button>
                                    <button type="button" class="btn btn-warning me-2">Warning</button>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-outline-primary me-2">Primary</button>
                                    <button type="button" class="btn btn-outline-secondary me-2">Secondary</button>
                                    <button type="button" class="btn btn-outline-info me-2">Info</button>
                                    <button type="button" class="btn btn-outline-success me-2">Success</button>
                                    <button type="button" class="btn btn-outline-danger me-2">Danger</button>
                                    <button type="button" class="btn btn-outline-warning me-2">Warning</button>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary btn-lg me-2">Primary</button>
                                    <button type="button" class="btn btn-secondary btn-lg me-2">Secondary</button>
                                    <button type="button" class="btn btn-info btn-lg me-2">Info</button>
                                    <button type="button" class="btn btn-success btn-lg me-2">Success</button>
                                    <button type="button" class="btn btn-danger btn-lg me-2">Danger</button>
                                    <button type="button" class="btn btn-warning btn-lg me-2">Warning</button>
                                </div>
                                <div class="mb-3">
                                    <button type="button" class="btn btn-primary btn-sm me-2">Primary</button>
                                    <button type="button" class="btn btn-secondary btn-sm me-2">Secondary</button>
                                    <button type="button" class="btn btn-info btn-sm me-2">Info</button>
                                    <button type="button" class="btn btn-success btn-sm me-2">Success</button>
                                    <button type="button" class="btn btn-danger btn-sm me-2">Danger</button>
                                    <button type="button" class="btn btn-warning btn-sm me-2">Warning</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        HTML;
    }
);


Modules::module("dev")->register_view(
    view : new ModuleView(
        name        : "colors",
        privileges  : null,
        settings    : new SettingsObject([
            "title"         => "Color Theme",
            "description"   => "Theme colors overview",
        ])
    ),
    render      : function() {

        /** @var Module $this */

        return <<<HTML
            <div class='container mt-3'>
                <div class='container' style="font-size:12px">
                    <div class="row mb-3">
                        <div class="col bg-primary-l5 p-2">bg-primary-l5</div>
                        <div class="col bg-primary-l4 p-2">bg-primary-l4</div>
                        <div class="col bg-primary-l3 p-2">bg-primary-l3</div>
                        <div class="col bg-primary-l2 p-2">bg-primary-l2</div>
                        <div class="col bg-primary-l1 p-2">bg-primary-l1</div>
                        <div class="col col-2 bg-primary p-2">bg-primary</div>
                        <div class="col bg-primary-d1 p-2">bg-primary-d1</div>
                        <div class="col bg-primary-d2 p-2">bg-primary-d2</div>
                        <div class="col bg-primary-d3 p-2">bg-primary-d3</div>
                        <div class="col bg-primary-d4 p-2">bg-primary-d4</div>
                        <div class="col bg-primary-d5 p-2">bg-primary-d5</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col bg-secondary-l5 p-2">bg-secondary-l5</div>
                        <div class="col bg-secondary-l4 p-2">bg-secondary-l4</div>
                        <div class="col bg-secondary-l3 p-2">bg-secondary-l3</div>
                        <div class="col bg-secondary-l2 p-2">bg-secondary-l2</div>
                        <div class="col bg-secondary-l1 p-2">bg-secondary-l1</div>
                        <div class="col col-2 bg-secondary p-2">bg-secondary</div>
                        <div class="col bg-secondary-d1 p-2">bg-secondary-d1</div>
                        <div class="col bg-secondary-d2 p-2">bg-secondary-d2</div>
                        <div class="col bg-secondary-d3 p-2">bg-secondary-d3</div>
                        <div class="col bg-secondary-d4 p-2">bg-secondary-d4</div>
                        <div class="col bg-secondary-d5 p-2">bg-secondary-d5</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col bg-info-l1 p-2">bg-info-l1</div>
                        <div class="col col-2 bg-info p-2">bg-info</div>
                        <div class="col bg-info-d1 p-2">bg-info-d1</div>

                        <div class="col bg-success-l1 p-2">bg-success-l1</div>
                        <div class="col col-2 bg-success p-2">bg-success</div>
                        <div class="col bg-success-d1 p-2">bg-success-d1</div>

                        <div class="col bg-warning-l1 p-2">bg-warning-l1</div>
                        <div class="col col-2 bg-warning p-2">bg-warning</div>
                        <div class="col bg-warning-d1 p-2">bg-warning-d1</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col bg-danger-l1 p-2">bg-danger-l1</div>
                        <div class="col col-2 bg-danger p-2">bg-danger</div>
                        <div class="col bg-danger-d1 p-2">bg-danger-d1</div>

                        <div class="col bg-elegant-l1 p-2">bg-elegant-l1</div>
                        <div class="col col-2 bg-elegant p-2">bg-elegant</div>
                        <div class="col bg-elegant-d1 p-2">bg-elegant-d1</div>

                        <div class="col bg-stylish-l1 p-2">bg-stylish-l1</div>
                        <div class="col col-2 bg-stylish p-2">bg-stylish</div>
                        <div class="col bg-stylish-d1 p-2">bg-stylish-d1</div>
                    </div>
                </div>
                <div class="container mt-3">
                    <div class="row mb-3">
                        
                        <div class="col text-color-light-f2 p-2">text-color-light-f2</div>
                        <div class="col text-color-light-f1 p-2">text-color-light-f1</div>
                        <div class="col text-color-light p-2">text-color-light</div>
                        <div class="col text-color-light-s1 p-2">text-color-light-s1</div>
                        <div class="col text-color-light-s2 p-2">text-color-light-s2</div>

                    </div>
                    <div class="row mb-3 bg-white">
                        
                        <div class="col text-color-dark-f2 p-2">text-color-dark-f2</div>
                        <div class="col text-color-dark-f1 p-2">text-color-dark-f1</div>
                        <div class="col text-color-dark p-2">text-color-dark</div>
                        <div class="col text-color-dark-s1 p-2">text-color-dark-s1</div>
                        <div class="col text-color-dark-s2 p-2">text-color-dark-s2</div>

                    </div>
                    <div class="row mb-3">

                        <div class="col text-primary-l1 p-2">text-primary-l1</div>
                        <div class="col col-2 text-primary p-2">text-primary</div>
                        <div class="col text-primary-d1 p-2">text-primary-d1</div>

                        <div class="col text-secondary-l1 p-2">text-secondary-l1</div>
                        <div class="col col-2 text-secondary p-2">text-secondary</div>
                        <div class="col text-secondary-d1 p-2">text-secondary-d1</div>

                    </div>
                    <div class="row mb-3">
                        <div class="col text-success-l1 p-2">text-success-l1</div>
                        <div class="col col-2 text-success p-2">text-success</div>
                        <div class="col text-success-d1 p-2">text-success-d1</div>

                        <div class="col text-danger-l1 p-2">text-danger-l1</div>
                        <div class="col col-2 text-danger p-2">text-danger</div>
                        <div class="col text-danger-d1 p-2">text-danger-d1</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col text-warning-l1 p-2">text-warning-l1</div>
                        <div class="col col-2 text-warning p-2">text-warning</div>
                        <div class="col text-warning-d1 p-2">text-warning-d1</div>

                        <div class="col text-info-l1 p-2">text-info-l1</div>
                        <div class="col col-2 text-info p-2">text-info</div>
                        <div class="col text-info-d1 p-2">text-info-d1</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col text-elegant-l1 p-2">text-elegant-l1</div>
                        <div class="col col-2 text-elegant p-2">text-elegant</div>
                        <div class="col text-elegant-d1 p-2">text-elegant-d1</div>

                        <div class="col text-stylish-l1 p-2">text-stylish-l1</div>
                        <div class="col col-2 text-stylish p-2">text-stylish</div>
                        <div class="col text-stylish-d1 p-2">text-stylish-d1</div>
                    </div>
                </div>
                <div class='container mt-3'>
                    <div class="alert alert-primary" role="alert">
                        A simple primary alert—check it out!
                    </div>
                    <div class="alert alert-secondary" role="alert">
                        A simple secondary alert—check it out!
                    </div>
                    <div class="alert alert-success" role="alert">
                        A simple success alert—check it out!
                    </div>
                    <div class="alert alert-danger" role="alert">
                        A simple danger alert—check it out!
                    </div>
                    <div class="alert alert-warning" role="alert">
                        A simple warning alert—check it out!
                    </div>
                    <div class="alert alert-info" role="alert">
                        A simple info alert—check it out!
                    </div>
                    <div class="alert alert-light" role="alert">
                        A simple light alert—check it out!
                    </div>
                    <div class="alert alert-dark" role="alert">
                        A simple dark alert—check it out!
                    </div>
                </div>
                <div class='container'>
                    <span class="badge rounded-pill bg-primary">Primary</span>
                    <span class="badge rounded-pill bg-secondary">Secondary</span>
                    <span class="badge rounded-pill bg-success">Success</span>
                    <span class="badge rounded-pill bg-danger">Danger</span>
                    <span class="badge rounded-pill bg-warning text-dark">Warning</span>
                    <span class="badge rounded-pill bg-info text-dark">Info</span>
                    <span class="badge rounded-pill bg-light text-dark">Light</span>
                    <span class="badge rounded-pill bg-dark">Dark</span>
                </div>
            </div>
        HTML;
    }
);