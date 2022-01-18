<x-layout>

    <div class="allert alert-access" role='alert'>
        <h2>New room is been created</h2>
    </div>
     {{-- //data coming from the backend --}}
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
     {{-- serve per isualizzare il codice Javascript --}}
    <ul id="output">

    </ul>

    <br>
    <br>
    <h3>Preview!</h3>
    <div id="local-media"></div>
    <button id="stop" class="btn btn-danger">Stop</button>
    
    {{-- JAVASCRIPT CODE FOR TWILIO NEW ROOM STARTS HERE--}}
    
    <script src="https://sdk.twilio.com/js/video/releases/2.15.2/twilio-video.min.js"></script>
    <script>
        // Funzioni di utilità, loggano una stringa nel blocco di outpot
        function log(str) {
           let li = document.createElement('li');
           li.textContent = str
           output.appendChild(li)  
        }

        // Serve per visualizzare un error 
        function error(str) {
           let li = document.createElement('li')
           li.style.color = 'red'
           li.textContent = str
           output.appendChild(li);
        }

        //Documentazione 
        // http://www.twilio.com/docs/video/javascript-v2-getting-started#join-a-room
        // Cattura sottosezione Video e la mette in una costante
        const Video = Twilio.Video;
        // Catturiamo anche connect // connect is a function of Video
        const connect = Video.connect;  
        // recuperiamo da Laravel il JWT Token, con React lo faremo tramite una chiamata API
        const jwt = "{{ $jwt }}";
         
        const room_name = "{{ $room_name }}";
        // Sfruttiamo connect per creare la prima connessione
        connect(jwt, { name: room_name })
        // Ritorna una Promise che si risolvera nel Tempo quando si risolve io ho una room una stanza
            .then( room => {
                log(`Successfully joined a Room: ${room}`)

                console.log(room);
                // Room è un oggetto di Twilio, Aulab ha analizzato l'oggetto room per capire il funzionamento di Twilio
                //Here We are going to create a Link that will allow me to join into the room
                
                let a = document.createElement('a')
                a.textContent = 'join the room'
                a.setAttribute('href', `{{ route('twilio.joinroom') }}?room_sid=${room.sid}`)
                a.setAttribute('target', '_blanck')

                // We create here an element li and we will appendChild 'a' to the 'li' element
                let li = document.createElement('li')
                li.appendChild(a)

                output.appendChild(li)

            //functions

            function closeTracks(tracks) {
                tracks.forEach(track => {
                   track.stop()
                   console.log(`track stoppata ${track}`)

                   // stacca il flusso di dati dall'elemento html      
                   const attachedElements = track.detach()

                   console.log(` elemento html disconesso ${attachedElements}`);
                   // here we will remove the element from the DOM
                   attachedElements.forEach(element => {
                       element.remove()
                       console.log(`elemento html rimosso: ${element}`);
                   })
                   
                });

            }
            //  Prede il stream di dati e fa una chiamata API per dire a Twilio di fermare questa stanza
            function closeRoom(room_sid, tracks) {
                   fetch(`/twilio/room/${room_sid}`, {
                       method: 'GET',
                   }).then(() => {
                       console.log('stanza chiusa');
                       closeTracks(tracks)
                   });
               
            }

            // CREATE THE LOCAL STREAMING // Flusso di dati del locale sarebbe il Volto, l'Audio, della persona che è connessa
            // http://www.twilio.com/docs/video/javascript-getting-started#set-up-local-media
            // Function of Video  "createLocalTracks"
            const createLocalTracks = Video.createLocalTracks;
             
            //Twilio crea la track locale video e audio
            let localAudioVideoTrack = createLocalTracks({
               audio: true,
               video: { width: 640 }
            });
           

             //devo fare lo screen share
            // http://www.twilio.com/blog/screen-sharing-javascript-twilio-programmable-video
            
            let screenSharingTrack = navigator.mediaDevices.getDisplayMedia();
            console.log(screenSharingTrack,createLocalTracks );
            // createLocalTracks and screenSharingTrack sono delle Promise
            //Stiamo usando il promise all per fare qualcosa quando le due Promise sono risolte 

            Promise
              .all([localAudioVideoTrack, screenSharingTrack])
                // qui facciamo un destracturing, localTracks è il risultato di localAudioVideoTrack, stream è lo strem del mio schermo 
              .then(([localTracks, stream ]) => {

                  //catturo la track dello screen sharing
                  // sto creando una nuova traccia video locale di twilio
                  screenTrack = new Twilio.Video.LocalVideoTrack(stream.getTracks()[0])

                  //unisco le tracks, l'udio e il video del mio streaming, ed il video dello schermo condiviso
                  const tracks = [...localTracks, screenTrack]
                  //   console.log(tracks);
                  //visualizzo la preview
                  const localVideoTrack = localTracks[1]
                  const localMediaContainer = document.getElementById('local-media');
                  localMediaContainer.appendChild(localVideoTrack.attach())
                  
                  // chiude la room 
                  document.querySelector('#stop').addEventListener('click', () => {
                      closeRoom(room.id, tracks)
                  })

                  //invio a twilio tutte le tracks :D
                 //   in questo momento, mi autentico con JWT, attraverso la funzione connect invio alla stanza il seguente flusso di dati, composto da tre tracks, audio, video del mio volto, e lo scream sharing. 
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
             // This function will activate when a new participant is logged
            room.on('participantConnected', participant  => {
                log(`A remote Participant connected: ${participant}`)
            });
       }, 
        // If there is an error we will see this 
       e => {
           error(`Unable to connect to Room: ${e.message}`)
       }
       )

   </script>
</x-layout>