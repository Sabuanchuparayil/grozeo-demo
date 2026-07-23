<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Test.aspx.cs" Async="true" Inherits="RetalineProAgent.Test" %>

<!DOCTYPE html>
<html>
<head>
    <title>Formula Builder</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .disabled {
            background-color: #cccccc;
            pointer-events: none;
            opacity: 0.6;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 40%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>Custom Fields Table</h2>

<!-- Table with custom fields and formula buttons -->
<table border="1" id="fieldsTable">
    <thead>
        <tr>
            <th>Field Name</th>
            <th>Formula</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Field 1</td>
            <td><button onclick="openFormulaPopup('Field 1', 'Field 1 + 5')">Edit Formula</button></td>
        </tr>
        <tr>
            <td>Field 2</td>
            <td><button onclick="openFormulaPopup('Field 2', '')">New Formula</button></td>
        </tr>
        <tr>
            <td>Field 3</td>
            <td><button onclick="openFormulaPopup('Field 3', 'Field 3 - 2')">Edit Formula</button></td>
        </tr>
    </tbody>
</table>

<!-- Modal for formula builder -->
<div id="formulaModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeFormulaPopup()">&times;</span>
        <h2>Formula Builder for <span id="fieldTitle"></span></h2>

        <!-- Static Dropdown to select fields -->
        <label for="fieldsDropdown">Select Field:</label>
        <select id="fieldsDropdown">
            <option value="">--Select Field--</option>
            <option value="Field 1">Field 1</option>
            <option value="Field 2">Field 2</option>
            <option value="Field 3">Field 3</option>
        </select>

        <!-- Operator buttons -->
        <div id="operators">
            <button id="addBtn" class="disabled" onclick="addOperator('+')">+</button>
            <button id="subBtn" class="disabled" onclick="addOperator('-')">-</button>
            <button id="mulBtn" class="disabled" onclick="addOperator('*')">*</button>
            <button id="divBtn" class="disabled" onclick="addOperator('/')">/</button>
        </div>

        <!-- Constant input -->
        <label for="constantInput">Enter Constant:</label>
        <input type="text" id="constantInput" disabled>
        <button id="addConstantBtn" class="disabled" onclick="addConstant()">Add Constant</button>

        <!-- Display the dynamic formula -->
        <h3>Formula: <span id="formulaDisplay"></span></h3>

        <!-- Buttons for validation and computation -->
        <button id="validateButton" class="disabled" onclick="validateFormula()">Validate Formula</button>
        <button id="saveButton" class="disabled" onclick="saveFormula()">Save Formula</button>
    </div>
</div>

<script>
    let formulaArray = [];
    let currentFormula = '';
    let currentField = '';

    function openFormulaPopup(fieldName, existingFormula) {
        currentField = fieldName;
        $('#fieldTitle').text(fieldName);

        // Preload existing formula if available
        if (existingFormula) {
            formulaArray = existingFormula.split(' ');
            $('#formulaDisplay').text(existingFormula);
            disableFieldDropdown();
            enableOperators();
            enableComputeValidateButtons();
        } else {
            resetBuilder(); // Reset if no formula
        }

        $('#formulaModal').css('display', 'block'); // Show the modal
    }

    function closeFormulaPopup() {
        $('#formulaModal').css('display', 'none'); // Close the modal
    }

    $('#fieldsDropdown').change(function () {
        const selectedField = $(this).val();
        if (selectedField && (formulaArray.length === 0 || isOperator(formulaArray[formulaArray.length - 1]))) {
            formulaArray.push(selectedField);
            updateFormulaDisplay();
            disableFieldDropdown();
            enableOperators();
            disableConstantInput();
        }
    });

    function addOperator(op) {
        if (formulaArray.length > 0 && !isOperator(formulaArray[formulaArray.length - 1])) {
            formulaArray.push(op);
            updateFormulaDisplay();
            disableOperators();
            enableConstantInput();
            enableFieldDropdown();
        }
    }

    function addConstant() {
        const constant = $('#constantInput').val();
        if (constant !== '' && !isNaN(constant)) {
            formulaArray.push(constant);
            updateFormulaDisplay();
            disableConstantInput();
            enableOperators();
            enableComputeValidateButtons();
        } else {
            alert('Please enter a valid number.');
        }
    }

    function updateFormulaDisplay() {
        $('#formulaDisplay').text(formulaArray.join(' '));
    }

    function validateFormula() {
        const formula = formulaArray.join(' ');
        if (formula.includes('/ 0')) {
            alert('Division by zero detected! Please correct the formula.');
        } else if (isOperator(formulaArray[formulaArray.length - 1])) {
            alert('Formula cannot end with an operator.');
        } else {
            alert('Formula is valid!');
        }
    }

    function saveFormula() {
        if (!isOperator(formulaArray[formulaArray.length - 1])) {
            currentFormula = formulaArray.join(' ');
            alert('Formula saved: ' + currentFormula);
            closeFormulaPopup();
        } else {
            alert('Cannot save a formula that ends with an operator.');
        }
    }

    // Helper functions
    function isOperator(value) {
        return ['+', '-', '*', '/'].includes(value);
    }

    function disableFieldDropdown() {
        $('#fieldsDropdown').prop('disabled', true);
    }

    function enableFieldDropdown() {
        $('#fieldsDropdown').prop('disabled', false);
    }

    function disableOperators() {
        $('#addBtn').addClass('disabled');
        $('#subBtn').addClass('disabled');
        $('#mulBtn').addClass('disabled');
        $('#divBtn').addClass('disabled');
    }

    function enableOperators() {
        $('#addBtn').removeClass('disabled');
        $('#subBtn').removeClass('disabled');
        $('#mulBtn').removeClass('disabled');
        $('#divBtn').removeClass('disabled');
    }

    function disableConstantInput() {
        $('#constantInput').prop('disabled', true);
        $('#addConstantBtn').addClass('disabled');
    }

    function enableConstantInput() {
        $('#constantInput').prop('disabled', false);
        $('#addConstantBtn').removeClass('disabled');
    }

    function disableComputeValidateButtons() {
        $('#validateButton').addClass('disabled');
        $('#saveButton').addClass('disabled');
    }

    function enableComputeValidateButtons()
    {
        $('#validateButton').removeClass('disabled');
        $('#saveButton').removeClass('disabled');
    }

    function resetBuilder() {
        formulaArray = [];
        currentFormula = '';
        $('#formulaDisplay').text('');
        $('#fieldsDropdown').val('');
        enableFieldDropdown();
        disableOperators();
        disableConstantInput();
        disableComputeValidateButtons();
    }
</script>

</body>
</html>

