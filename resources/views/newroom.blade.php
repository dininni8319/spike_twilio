<x-layout>

    <div class="allert alert-access" role='alert'>
        <h2>New room is been created</h2>
    </div>

    <h3>Dati ricevuti dal backend</h3>
    <div class="table-responsive">
        <table class="lign-middle">
            <tr>
                <th class="align-top p-2" scope="row" style="white-space: : nowrap">Room Name</th>
                <td class="align-top p-2">{{ $room_name}}</td>
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
    <h3>Preview!</h3>
    <div id="local-media"></div>
    <button id="stop" class="btn btn-danger">Stop</button>
    
    JAVASCRIPT CODE FOR TWILIO NEW ROOM
    
    <script src="https://sdk.twilio.com/js/video/releases/2.15.2/twilio-video.min.js"></script>
   <script>

       function log(str) {
           let li = document.createElement('li');
           li.textContent = str

           output.appendChild(li)
           
       }

       function error(str) {
           let li = document.createElement('li')
           li.style.color = 'red'
           li.textContent = str

           output.appendChild(li);
       }

       //Documentazione 
       // http://www.twilio.com/docs/video/javascript-v2-getting-started#join-a-room

       const Video = Twilio.Video;

       const connect = Video.connect;  // connect is a function of Video

       const jwt = "{{ $jwt}}";

       const room_name = "{{ $room_name }}";
      console.log(room_name);
       connect(jwt, { name: room_name }).then( room => {
           log(`Successfully joined a Room: ${room}`)

           // Room è un oggetto di Twilio
           //We are going to create a Link that will allow me to join in the room

           let a = document.createElement('a')
           a.textContent = 'join the room'
           a.setAttribute('href', `{{ route('twilio.joinroom') }}?room_sid=${room.sid}`)
           a.setAttribute('target', '_blanck')

           let li = document.createElement('li')
           li.appendChild(a)

           output.appendChild(li)

           //functions

           function closeTracks(tracks) {
               tracks.forEach(track => {
                   track.stop()
                   console.log(`track stoppata ${track}`)

                   const attachedElements = track.detach()

                   console.log(` elemento html disconesso ${attachedElements}`);

                   attachedElements.forEach(element => {
                       element.remove()
                       console.log(`elemento html rimosso: ${element}`);
                   })

               });

           }

           function closeRoom(room_sid, tracks) {
                   fetch(`/twilio/room/${room_sid}`, {
                       method: 'GET',
                   }).then(() => {
                       console.log('stanza chiusa');
                       closeTracks(tracks)
                   });
               
           }
           // CREATE THE LOCAL STREAMING
           // http://www.twilio.com/docs/video/javascript-getting-started#set-up-local-media
           const createLocalTracks = Video.createLocalTracks;

           //Twilio crea la track locale video e audio
           let localAudioVideoTrack = createLocalTracks({
               audio: true,
               video: { width: 640 }
           });
           

           //devo fare lo screen share
           // http://www.twilio.com/blog/screen-sharing-javascript-twilio-programmable-video

           let screenSharingTrack = navigator.mediaDevices.getDisplayMedia();

           Promise
              .all([localAudioVideoTrack, screenSharingTrack])
              .then(([localTracks, stream ]) => {


                  //catturo la track dello screen sharing
                  screenTrack = new Twilio.Video.LocalVideoTrack(stream.getTracks()[0])

                  //unisco le tracks
                  const tracks = [...localTracks, screenTrack]

                  //visualizzo la preview
                  const localVideoTrack = localTracks[1]
                  const localMediaContainer = document.getElementById('local-media');
                  localMediaContainer.appendChild(localVideoTrack.attach())
                  
                  // chiude la room 
                  document.querySelector('#stop').addEventListener('click', () => {
                      closeRoom(room.id, tracks)
                  })
                  //invio a twilio tutte le tracks :D
                  return connect(jwt, {
                      name: '{{ $room_name }}',
                      tracks: tracks,
                  })

              })
              .then(room => {
                  log(`Local Track With Video and Audio Connected to the Room: ${room.name}`)
              })
              .catch(e => {
                  log('se vuoi fare streaming devi per forza condividere il tuo schermo')
                  console.log(e);
                  
                  //io devo distaccare tutto perche l'utente deve rifare lo share dello screen

                  localAudioVideoTrack.then(tracks => {
                      closeRoom(room.sid, tracks)
                  })
                  
              });

            room.on('participantConnected', participant  => {
                log(`A remote Participant connected: ${participant}`)
              });
       }, 

       e => {
           error(`Unable to connect to Room: ${e.message}`)
       }
       )

   </script>
</x-layout>