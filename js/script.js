document.addEventListener('DOMContentLoaded', function() {
    // Populate Year Dropdown
    const yearSelect = document.getElementById('year');
    if (yearSelect) {
        const currentYear = new Date().getFullYear();
        for (let year = currentYear; year <= 2100; year++) {
            const option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearSelect.appendChild(option);
        }
        // Pre-select current year by default
        if(yearSelect.options.length > 1 && !yearSelect.value) { // if placeholder shown
             yearSelect.value = currentYear;
        }
    }

    // Set current year in footer
    const currentYearSpan = document.getElementById('currentYear');
    if (currentYearSpan) {
        currentYearSpan.textContent = new Date().getFullYear();
    }

    // Class selection changes subject visibility and requirements
    const classSelection = document.getElementById('class_selection');
    if (classSelection) {
        classSelection.addEventListener('change', function() {
            const selectedClass = this.value;
            updateSubjectFields(selectedClass);
        });
        // Call once on load in case a class is pre-selected (e.g. by browser)
         if(classSelection.value) {
            updateSubjectFields(classSelection.value);
        }
    }
});

function updateSubjectFields(selectedClass) {
    // Selectors for all relevant sections
    const commonSubjectInitials = document.querySelectorAll('.common-subject-initials');
    const p1p3SubjectInitials = document.querySelectorAll('.p1p3-subject-initials');
    const p4p7SubjectInitials = document.querySelectorAll('.p4p7-subject-initials');
    const nurserySubjectInitials = document.querySelectorAll('.nursery-subject-initials');
    const nurserySpecificFieldsCard = document.getElementById('nursery-specific-fields');

    const marksFileLabel = document.getElementById('marks_excel_file_label');
    const defaultMarksFileLabelText = "Marks Excel File (.xlsx):";

    // Update the main marks file label
    if (marksFileLabel) {
        if (selectedClass && selectedClass !== "") {
            marksFileLabel.textContent = selectedClass + " " + defaultMarksFileLabelText;
        } else {
            marksFileLabel.textContent = defaultMarksFileLabelText;
        }
    }

    // Helper to set visibility and requirement for teacher initial fields
    function setInitialFields(elements, display, required) {
        elements.forEach(block => {
            block.style.display = display ? '' : 'none';
            const textInputs = block.querySelectorAll('input[type="text"]');
            textInputs.forEach(input => {
                // Special case for optional Kiswahili
                if (input.id === 'kiswahili_initials') {
                    input.required = false; // Always optional
                } else {
                     input.required = required && display;
                }
            });
        });
    }

    // Helper for the nursery-specific info card
    function setNurseryInfoCard(display, required) {
        if (nurserySpecificFieldsCard) {
            nurserySpecificFieldsCard.style.display = display ? '' : 'none';
            const textInputs = nurserySpecificFieldsCard.querySelectorAll('input[type="text"]');
            textInputs.forEach(input => {
                // These fields are required when the section is visible
                input.required = required && display;
            });
        }
    }

    const nurseryClasses = ['Baby Class', 'Middle Class', 'Top Class'];

    if (nurseryClasses.includes(selectedClass)) {
        // Nursery selected
        setNurseryInfoCard(true, true);
        setInitialFields(nurserySubjectInitials, true, true);
        setInitialFields(commonSubjectInitials, false, false);
        setInitialFields(p1p3SubjectInitials, false, false);
        setInitialFields(p4p7SubjectInitials, false, false);
    } else if (selectedClass.startsWith('P1') || selectedClass.startsWith('P2') || selectedClass.startsWith('P3')) {
        // Lower Primary selected
        setNurseryInfoCard(false, false);
        setInitialFields(nurserySubjectInitials, false, false);
        setInitialFields(commonSubjectInitials, true, true);
        setInitialFields(p1p3SubjectInitials, true, true);
        setInitialFields(p4p7SubjectInitials, false, false);
    } else if (selectedClass.startsWith('P4') || selectedClass.startsWith('P5') || selectedClass.startsWith('P6') || selectedClass.startsWith('P7')) {
        // Upper Primary selected
        setNurseryInfoCard(false, false);
        setInitialFields(nurserySubjectInitials, false, false);
        setInitialFields(commonSubjectInitials, true, true);
        setInitialFields(p1p3SubjectInitials, false, false);
        setInitialFields(p4p7SubjectInitials, true, true);
    } else {
        // No class or unknown class selected
        setNurseryInfoCard(false, false);
        setInitialFields(nurserySubjectInitials, false, false);
        setInitialFields(p1p3SubjectInitials, false, false);
        setInitialFields(p4p7SubjectInitials, false, false);
        setInitialFields(commonSubjectInitials, true, false); // Show common but don't require
    }
}

// Call updateSubjectFields on page load if a class is already selected (e.g., form resubmission with errors)
const initialClass = document.getElementById('class_selection')?.value;
if (initialClass) {
    updateSubjectFields(initialClass);
} else {
    // Default state: hide class-specific subjects until a class is chosen
    updateSubjectFields('');
}
