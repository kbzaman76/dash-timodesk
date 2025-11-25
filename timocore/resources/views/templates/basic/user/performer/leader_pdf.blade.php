<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ $pageTitle }}</title>

  <style>
    *{
      padding: 0;
      margin: 0;
  }
  @page {
      size: 5.83in 8.27in;

  }
  header {
      position: fixed;
      top: 0px;
      left: 0px;
      right: 0px;
      background-image: url({{asset('assets/images/leaderboard/bg.png')}});
      background-repeat: no-repeat;
      width:100%;
      height:100%;
      background-size: cover;
      z-index: -999;
  }
  body {
    margin: 0;
}


.main-wrapper{
    text-align: center;
}

.org{
    margin-top:120px;
    font-size: 90px;
    line-height: 90px;
    font-weight: 800;
    font-family: "Urbanist", sans-serif;
    color: #ff6900;
}
.date{

    margin-top:150px;
    font-size: 80px;
    line-height: 80px;
    font-weight: 200;
    font-family: "Urbanist", sans-serif;
    color: #030712;
}
.image{

    margin-top:484px;
}
.user{
    margin-top:100px;
    font-size: 70px;
    line-height: 70px;
    font-weight: 800;
    font-family: "Urbanist", sans-serif;
    color: #030712;
    text-transform: uppercase;
}
</style>
</head>

<body>
    <header></header>
    <div class="main-wrapper">
        <div class="org">{{ $organization->name }}</div>
        <div class="date">{{ $printDate }}</div>
        <div class="image"><img src="{{ $topMember->user->image_url }}" style="width:930px"></div>
        <div class="user">{{ toTitle($topMember->user->fullname) }}</div>
    </div>
</body>
</html>
