<x-layout>

    <div class="allert alert-access" role='alert'>
        <h2>New room is been created</h2>
    </div>

    <h3>Dati ricevuti dal backend</h3>
    <div class="table-responsive">
        <table class="lign-middle">
            <tr>
                <th class="align-top p-2" scope="row" style="white-space: : nowrap">Room Name</th>
                <td class="align-top p-2">{{ $identity}}</td>
            </tr>
        </table>
        <table class="lign-middle">
            <tr>
                <th class="align-top p-2" scope="row" style="white-space: : nowrap">Room Sid</th>
                <td class="align-top p-2">{{ $room_sid}}</td>
            </tr>
        </table>
        <table class="lign-middle">
            <tr>
                <th class="align-top p-2" scope="row" style="white-space: : nowrap">JWT</th>
                <td class="align-top p-2">{{ $jwt}}</td>
            </tr>
        </table>
    </div>

</x-layout>

