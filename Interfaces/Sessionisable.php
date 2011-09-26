<?php
namespace Art\Interfaces {
    interface Sessionisable {
        function useSession($bool);
        function getSession();
        function resetSession();
    }
}