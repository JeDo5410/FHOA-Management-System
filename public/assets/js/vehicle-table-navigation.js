// Vehicle Table Arrow Key Navigation
document.addEventListener('DOMContentLoaded', function() {
    initializeTableNavigation();
});

function initializeTableNavigation() {
    // Get the vehicle table
    const vehicleTable = document.querySelector('.vehicle-table');
    if (!vehicleTable) return;

    // All vehicle table rows
    const rows = Array.from(vehicleTable.querySelectorAll('tr.vehicle-row'));
    
    // Set up navigation for each cell in the table
    rows.forEach((row, rowIndex) => {
        // Get all input and select elements in the row
        const cells = Array.from(row.querySelectorAll('input, select'));
        
        cells.forEach((cell, cellIndex) => {
            // Add keydown event listener
            cell.addEventListener('keydown', function(e) {
                // Only handle arrow keys
                if (!['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(e.key)) return;
                
                let targetRow = rowIndex;
                let targetCell = cellIndex;
                let handled = false;
                
                switch (e.key) {
                    case 'ArrowUp':
                        if (rowIndex > 0) {
                            targetRow = rowIndex - 1;
                            handled = true;
                        }
                        break;
                    case 'ArrowDown':
                        if (rowIndex < rows.length - 1) {
                            targetRow = rowIndex + 1;
                            handled = true;
                        }
                        break;
                    case 'ArrowLeft':
                        if (cellIndex > 0) {
                            targetCell = cellIndex - 1;
                            handled = true;
                        } else if (rowIndex > 0) {
                            // Go to the last cell of the previous row
                            targetRow = rowIndex - 1;
                            const previousRowCells = Array.from(rows[targetRow].querySelectorAll('input, select'));
                            targetCell = previousRowCells.length - 1;
                            handled = true;
                        }
                        break;
                    case 'ArrowRight':
                    case 'Tab':
                        if (cellIndex < cells.length - 1) {
                            targetCell = cellIndex + 1;
                            handled = true;
                        } else if (rowIndex < rows.length - 1) {
                            // Go to the first cell of the next row
                            targetRow = rowIndex + 1;
                            targetCell = 0;
                            handled = true;
                        }
                        break;
                }
                
                // If we're handling the event, move focus to the target cell
                if (handled) {
                    e.preventDefault(); // Prevent default behavior of arrow keys
                    
                    // Find the target element and focus it
                    const targetCells = Array.from(rows[targetRow].querySelectorAll('input, select'));
                    if (targetCells[targetCell]) {
                        targetCells[targetCell].focus();
                        
                        // For text inputs, place cursor at the end
                        if (targetCells[targetCell].tagName === 'INPUT' && 
                            targetCells[targetCell].type === 'text') {
                            const length = targetCells[targetCell].value.length;
                            targetCells[targetCell].setSelectionRange(length, length);
                        }
                    }
                }
            });
        });
    });
    
    // Add visual indicator for focused cell
    vehicleTable.addEventListener('focusin', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
            // Find the parent td element
            const cell = e.target.closest('td');
            if (cell) {
                cell.classList.add('focused-cell');
            }
        }
    });
    
    vehicleTable.addEventListener('focusout', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') {
            // Remove focus highlight from all cells
            vehicleTable.querySelectorAll('td.focused-cell').forEach(cell => {
                cell.classList.remove('focused-cell');
            });
        }
    });
    
    // Add this CSS to highlight the active cell
    const style = document.createElement('style');
    style.textContent = `
        .vehicle-table td.focused-cell {
            background-color: rgba(13, 110, 253, 0.05);
        }
        .vehicle-table input:focus, 
        .vehicle-table select:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            border-color: #86b7fe;
        }
    `;
    document.head.appendChild(style);
}