
function player(table-id)
{
    var _tableId, _table, 
        _fields, _headers, 
        _defaultText;

    //
    // Append an item to the end of the playlist
    //
    function playlist_item_append(item)
    {
        var table = document.getElementById('play-table');

        var tr   = table.appendChild(document.createElement('tr'));
        var cell = tr.appendChild(document.createElement('td'));
        cell.innerHTML=item;
    }
    

    //
    // Initialization
    //
    return{

    }

}



