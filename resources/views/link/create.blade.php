@extends('layouts.app')

@section('content')
<script>
$(document).ready(function() {
  $("#tag").select2({
    tags: true,
    tokenSeparators: [',', ' ']
  });

  $("#link").on("input", function(){
    // Print entered value in a div box
    

    let link = $('#link').val();
    $.ajax({
      url: "/links/check-unique",
      type:"POST",
      data:{
        "_token": "{{ csrf_token() }}",
        link:link 
      },
      success:function(response){
        if(response.status == false) {
          toastr.warning(response.message);
          let data = response.data;
          data = JSON.parse(data); //convert to javascript array
          values = '';
          $.each(data,function(key,value){
            values+="<option value='"+value.id+"' selected>"+value.name+"</option>";
          });
          $("#tag").html(values);
        }
      },
      error: function(response) {
        toastr.error(response.message);
      },
    });
  });


});

</script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form id="form" action="{{url('links/insert')}}" method="post">
                      @csrf
                        <div class="form-group">
                          <label for="link">Full link</label>
                          <input type="text" class="form-control" id="link" name="link" placeholder="Link">
                        </div>

                        <div class="form-group">
                          <label for="inputPropery">tag Name</label>
                          {{-- <input type="text" class="form-control" id="inputPropery" aria-describedby="tagHelp" placeholder="Enter email"> --}}
                          <select style="width:100%;"   id="tag" name="tag" multiple="">
                            <option></option>
                          </select>
                          {{-- <small id="tagHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> --}}
                        </div>

                        <div class="form-group">
                          <label for="father_name">Father Name</label>
                          <input type="text" class="form-control" id="father_name" name="father_name" placeholder="Father Name">
                        </div>
                        <div class="form-group">
                          <label for="mother_name">Mother Name</label>
                          <input type="text" class="form-control" id="mother_name" name="mother_name" placeholder="Mother Name">
                        </div>
                        <div class="form-group">
                          <label for="photo">Photo</label>
                          <input type="file" id="photo" name="photo" class="form-control">
                        </div>
                        <div class="form-group">
                          <label for="birth_certificate_number">Birth Certificate Number</label>
                          <input type="text" class="form-control" id="birth_certificate_number" name="birth_certificate_number" placeholder="Birth Certificate Number">
                        </div>
                        <div class="form-group">
                          <label for="nid">National identification number</label>
                          <input type="text" class="form-control" id="nid" name="nid" placeholder="NID">
                        </div>
                        <div class="form-group">
                          <label for="iris">IRIS</label>
                          <input type="text" class="form-control" id="iris" name="iris" placeholder="IRIS">
                        </div>
                        <div class="form-group">
                          <label for="dna">DNA</label>
                          <input type="text" class="form-control" id="dna" name="dna" placeholder="DNA">
                        </div>
                        <div class="form-group">
                          <label for="national_health_certificate_number">National Health certificate number</label>
                          <input type="text" class="form-control" id="national_health_certificate_number" name="national_health_certificate_number" placeholder="National Health certificate number">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready( function() {
  toastr.options =
  {
  	"closeButton" : true,
  	"progressBar" : true
  };

  $.getJSON("/links/listtags",function(response){
    let data = response.data;
    data = JSON.parse(data); //convert to javascript array
    values = '';
    $.each(data,function(key,value){
      
      values+="<option value='"+value.id+"'>"+value.name+"</option>";
    });
    $("#tag").html(values); 
  });


  $("#form").submit(function(e){
    
    e.preventDefault();

    let link = $('#link').val();
    let tags = $('#tag').val();
    let mother_name = $('#mother_name').val();
    let photo = $('#photo').val();
    let nid = $('#nid').val();
    let birth_certificate_number = $('#birth_certificate_number').val();
    let iris = $('#iris').val();
    let dna = $('#dna').val();
    let national_health_certificate_number = $('#national_health_certificate_number').val();

    $.ajax({
      url: "/links/insert",
      type:"POST",
      data:{
        "_token": "{{ csrf_token() }}",
        link:link,
        tags:tags,
        mother_name:mother_name,
        photo:photo,
        nid:nid,
        birth_certificate_number:birth_certificate_number,
        iris:iris,
        dna:dna,
        national_health_certificate_number:national_health_certificate_number    
      },
      success:function(response){
        toastr.success(response.message);
        // window.location.href = "{{ route('links.list','message=New links added ...') }}";
        // if(response.status)
        //   $('#form')[0].reset();
      },
      error: function(response) {
        toastr.error(response.message);
      },
    });
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection
