<x-layout>

    <div class="allert alert-access" role='alert'>
        <h2>Join to the Room!</h2>
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


    <br>
    <br>

    <h3>JS Output!</h3>

    <ul id="output">

    </ul>

    <br>
    <br>
    <h3>Streamer!</h3>
    <div id="remote-media-div"></div>
    <button id="stop" class="btn btn-danger">Stop</button>
    
    {{-- JAVASCRIPT CODE FOR TWILIO NEW ROOM --}}
    
    <script src="https://sdk.twilio.com/js/video/releases/2.15.3/twilio-video.min.js"></script>
    <script>

        function log(str) {
           let li = document.createElement('li');
           li.textContent = str

           output.appendChild(li)   
        }

        function errorF(str) {
           let li = document.createElement('li')
           li.style.color = 'red'
           li.textContent = str

           output.appendChild(li);
        }

        //Documentazione 
        // http://www.twilio.com/docs/video/javascript-v2-getting-started#join-a-room

        const Video = Twilio.Video;

        const connect = Video.connect;  // connect is a function of Video

        const jwt = "{{ $jwt }}";

        connect(jwt, { name: `{{ $room_sid }}`}).then(room => {
             log(`Successfully joined in the Room: ${room}`);
             
             //Gestisco gli eventuali partecipanti gia collegati
             const localParticipant = room.localParticipant;
             
             //Visual of all participant connected 
             room.participants.forEach(participant => {
                log(`Participant already connected: ${participant.identity}`);
             });
             /// Event, when a new participant is connected, this event will be triggered
             room.on('participantConnected', participant => {
                 log(`A remote Partecipant connected: ${participant}`)
             });

             return room;
        }).then(room => {
             //Vado a visualizzare le track trasmesse nella room
             // http://www.twilio.com/docs/video/javascript-v2-getting-started#diplay-a-remote-participants-video
            // Here we catch the streamer 
            let streamer = Array.from(room.participants.values()).filter(p => p.identity == 'Streamer')[0];
            
            // console.log(room.participants.values(), 'partecipants');
            // console.log(streamer.tracks, 'tracks');
            streamer.tracks.forEach(publication => {
                // console.log(publication);
                publication.on('subscribed', track => {
                    document.getElementById('remote-media-div').appendChild(track.attach());
                })
            });

            room.on('disconnected', room => {
                log(`mi sono disconesso`)
                //detach the local media elements
                room.localParticipant.tracks.forEach(publication => {
                    const attachedElements = publication.track.detach();
                    attachedElements.foreach(element => element.remove());
                    log(`stacco i video :D`);
                });
            });

            //chiude room   
            document.querySelector('#stop').addEventListener('click', () => {
                console.log('click');
                room.disconnect();
            })
        })

        .catch(error => {
            errorF(`Unable to connect to the Room: ${error.message}`)

        });
    </script>
    
</x-layout>

