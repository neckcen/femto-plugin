/*
Title: PHP legacy Plugin Demonstration
Description: A demonstration of the PHP legacy plugin in action.
PHP: index.php
No-Cache: page
*/

PHP legacy Plugin Demonstration
===============================

Variable substitution
---------------------
This server's PHP version is: %version%

Form validation and persistence
-------------------------------
<form novalidate method="POST" class="forms" id="testform">
    <label>
        Required field
        <input type="text" name="required" required class="width-60">
    </label>
    <label>
        Pattern matching (must contain "=")
        <input type="text" name="pattern" pattern="[^=]*=[^=]*" class="width-60">
    </label>
    <label>
        Maximum length (3 characters)
        <input type="text" name="maxlength" maxlength="3" class="width-60">
    </label>
    Checkboxes
    <ul class="forms-list">
        <li>
           <input checked type="checkbox" name="checkbox1" id="checkbox1">
           <label for="checkbox1">Checkbox 1.</label>
        </li>
        <li>
           <input type="checkbox" name="checkbox2" id="checkbox2">
           <label for="checkbox2">Checkbox 2.</label>
        </li>
    </ul>
    Radio
    <ul class="forms-list">
        <li>
           <input type="radio" name="radio" id="radio1" value="1">
           <label for="radio1">Radio 1.</label>
        </li>
        <li>
           <input checked type="radio" name="radio" id="radio2" value="2">
           <label for="radio2">Radio 2.</label>
        </li>
        <li>
           <input type="radio" name="radio" id="radio3" value="3">
           <label for="radio3">Radio 3.</label>
        </li>
    </ul>
    <label>
        Select
        <select name="select" class="width-60">
            <option selected></option>
            <option>Option 1</option>
            <option>Option 2</option>
        </select>
    </label>
    <label>
        Number (between 1 and 100)
        <input type="number" min="1" max="100" name="number" class="width-60">
    </label>
    <label>
        Range (between 1 and 100)
        <input type="range" min="1" max="100" name="range" class="width-60">
    </label>
    <label>
        Email
        <input type="email" name="email" class="width-60">
    </label>
    <label>
        Textarea
        <textarea name="textarea" rows="10" class="width-60"></textarea>
    </label>
    <p>
        <input class="btn btn-green width-100" type="submit" name="submit" value="Submit">
    </p>
</form>
