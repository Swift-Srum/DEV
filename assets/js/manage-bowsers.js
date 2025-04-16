async function editBowser(id) {
    const row = document.querySelector(`tr[data-id="${id}"]`);
    
    try {
        const response = await fetch(`/admin/get-bowser.php?id=${id}`);
        const bowser = await response.json();
        
        // Show edit form with bowser details
        const name = prompt('Enter new name:', bowser.name);
        const model = prompt('Enter new model:', bowser.model);
        const capacity = prompt('Enter new capacity (L):', bowser.capacity_litres);
        const supplier = prompt('Enter new supplier:', bowser.supplier_company);
        const postcode = prompt('Enter new postcode:', bowser.postcode);
        const status = prompt('Enter new status (On Depot/Dispatched/In Transit/Maintenance Requested/Under Maintenance/Ready/Out of Service):', bowser.status_maintenance);
        
        if (name && model && capacity && supplier && postcode && status) {
            const response = await fetch('/admin/update-bowser.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}&name=${encodeURIComponent(name)}&model=${encodeURIComponent(model)}&capacity=${encodeURIComponent(capacity)}&supplier=${encodeURIComponent(supplier)}&postcode=${encodeURIComponent(postcode)}&status=${encodeURIComponent(status)}`
            });
            
            if (response.ok) {
                location.reload();
            } else {
                alert('Failed to update bowser');
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred');
    }
}

async function deleteBowser(id) {
    if (!confirm('Are you sure you want to delete this bowser? This action cannot be undone.')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('/admin/delete-bowser.php', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Remove the row from the table
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (row) {
                row.remove();
            }
            alert('Bowser deleted successfully');
        } else {
            throw new Error(data.error || 'Failed to delete bowser');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'An error occurred while deleting the bowser');
    }
}